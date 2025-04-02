<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use App\Models\DB;
use Slim\Exception\HttpNotFoundException;
use Tuupola\Middleware\CorsMiddleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    $origin = $request->getHeaderLine('Origin');
    if (!empty($origin)) {
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }
    return $response->withStatus(200);
});

$app->add(new CorsMiddleware([
    "origin" => ["http://localhost:41064"], // Permite solicitudes desde este origen
    "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"], // Métodos permitidos
    "headers.allow" => ["Content-Type", "Authorization"], // Encabezados permitidos
    "headers.expose" => ["Authorization"], // Si necesitas exponer algún encabezado
    "credentials" => true, // Si necesitas trabajar con cookies o tokens
]));

include "./admin.php";
include "./owners.php";
include "./waiters.php";
include "./dinners.php";
include "./JWT.php";

$authMiddleware = function ($request, $handler) {
    $response = new \Slim\Psr7\Response();
    
    $header = $request->getHeaderLine('Authorization');
    if (empty($header)) {
        $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    try {
        $token = str_replace('Bearer ', '', $header);
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        
        $request = $request->withAttribute('user', $decoded);
        return $handler->handle($request);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Token inválido']));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
};

$app->add(function ($request, $handler) use ($authMiddleware) {
    $route = $request->getUri()->getPath();
    $method = $request->getMethod();

    if ($method === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        $origin = $request->getHeaderLine('Origin');
        if (!empty($origin)) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->withStatus(200);
        }
        return $response;
    }

    if ($route !== '/get_sesion') {
        return $authMiddleware($request, $handler);
    }

    return $handler->handle($request);
});

$app->post('/get_sesion', function (Request $request, Response $response) {
    $input = $request->getBody()->getContents(); 
    $data = json_decode($input, true);

    if ($data === null) {
        error_log('Failed to parse JSON: ' . json_last_error_msg());
        $response->getBody()->write(json_encode(["error" => "Invalid JSON format"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $username = filter_var($data["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($data["password"], FILTER_SANITIZE_STRING);

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $stmt = $conn->prepare("SELECT id_role FROM UsersRoles WHERE id_user = :id_user");
            $stmt->bindParam(':id_user', $user['id_user']);
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $payload = [
                'user_id' => $user['id_user'],
                'username' => $user['username'],
                'restaurant' => $user['id_restaurant'],
                'roles' => $roles,
                'exp' => time() + (60 * 60)
            ];

            $token = JWT::encode($payload, JWT_SECRET, 'HS256');

            $data = [
                "status" => true,
                "token" => $token,
                "roles" => $roles,
                "id" => $user['id_user'],
                "restaurant" => $user['id_restaurant']
            ];
        } else {
            $data = [
                "status" => false,
                "message" => 'Credenciales inválidas.',
                "Usuario" => $username,
                "Contraseña" => $password
            ];
        }

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = ["message" => "Error en la base de datos"];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $data = ['error' => 'Ruta no encontrada'];
    $response->getBody()->write(json_encode($data));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

$app->run();
