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

define('BASE_URL', 'http://192.168.1.206:41063');

include "./admin.php";
include "./owners.php";
include "./waiters.php";
include "./dinners.php";
include "./prepStation.php";
include "./JWT.php";

// $authMiddleware = function ($request, $handler) {
//     $response = new \Slim\Psr7\Response();
    
//     $header = $request->getHeaderLine('Authorization');
//     if (empty($header)) {
//         $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
//         return $response
//             ->withStatus(401)
//             ->withHeader('Content-Type', 'application/json');
//     }

//     try {
//         $token = str_replace('Bearer ', '', $header);
//         $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        
//         $request = $request->withAttribute('user', $decoded);
//         return $handler->handle($request);
//     } catch (Exception $e) {
//         $response->getBody()->write(json_encode(['error' => 'Token inválido']));
//         return $response
//             ->withStatus(401)
//             ->withHeader('Content-Type', 'application/json');
//     }
// };

// $app->add(function ($request, $handler) use ($authMiddleware) {
//     $route = $request->getUri()->getPath();
//     $method = $request->getMethod();

//     if ($method === 'OPTIONS') {
//         $response = new \Slim\Psr7\Response();
//         $origin = $request->getHeaderLine('Origin');
//         if (!empty($origin)) {
//             $response = $response
//                 ->withHeader('Access-Control-Allow-Origin', $origin)
//                 ->withHeader('Access-Control-Allow-Credentials', 'true')
//                 ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
//                 ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
//                 ->withStatus(200);
//         }
//         return $response;
//     }

//     if ($route === '/get_sesion') {
//         return $handler->handle($request);
//     }

//     return $authMiddleware($request, $handler);
// });

$app->post('/get_sesion', function (Request $request, Response $response) {
    try {
        // Clear any previous output buffers to start clean
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $input = $request->getBody()->getContents();
        $data = json_decode($input, true);

        if ($data === null) {
            $error = [
                "status" => false, 
                "message" => "Formato JSON inválido"
            ];
            return createJsonResponse($response, $error, 400);
        }

        // Check if required fields are present
        if (!isset($data["username"]) || !isset($data["password"])) {
            $missing = [];
            if (!isset($data["username"])) $missing[] = "username";
            if (!isset($data["password"])) $missing[] = "password";
            
            $error = [
                "status" => false, 
                "message" => "Campos requeridos faltantes: " . implode(', ', $missing)
            ];
            return createJsonResponse($response, $error, 400);
        }

        $username = $data["username"];
        $password = $data["password"];

        // Authentication logic
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Simplify the response data to reduce potential issues
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful - Get user roles (just the IDs)
            $stmt = $conn->prepare("SELECT id_role FROM UsersRoles WHERE id_user = :id_user");
            $stmt->bindParam(':id_user', $user['id_user']);
            $stmt->execute();
            $roleIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $payload = [
                'user_id' => intval($user['id_user']),
                'username' => $user['username'],
                'restaurant' => intval($user['id_restaurant']),
                'roles' => $roleIds,
                'exp' => time() + (60 * 60)
            ];

            $token = JWT::encode($payload, JWT_SECRET, 'HS256');

            $responseData = [
                "status" => true,
                "token" => $token,
                "roles" => $roleIds,
                "id" => intval($user['id_user']),
                "restaurant" => intval($user['id_restaurant']),
                "username" => $user['username'],
                "name" => $user['name'],
            ];

            
            return createJsonResponse($response, $responseData, 200);
        } else {
            // Invalid credentials
            $error = [
                "status" => false,
                "message" => "Credenciales inválidas."
            ];
            return createJsonResponse($response, $error, 401);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = ["status" => false, "message" => "Error en la base de datos"];
        return createJsonResponse($response, $error, 500);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        $error = ["status" => false, "message" => "Error en el servidor"];
        return createJsonResponse($response, $error, 500);
    }
});

// Add this helper function at the top of the file, after the imports
function createJsonResponse($response, $data, $status) {
    // Create a fresh response object to avoid any potential issues with the existing one
    $response = new \Slim\Psr7\Response($status);
    
    // Simplify the JSON encoding to avoid potential issues
    $json = json_encode($data);
    
    // Handle JSON encoding errors
    if ($json === false) {
        error_log("JSON encoding error: " . json_last_error_msg());
        $json = '{"status":false,"message":"Error interno del servidor"}';
    }
    
    // Keep headers minimal but include what's necessary for proper handling
    $response = $response
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Content-Length', strlen($json));
    
    // Write the response body
    $response->getBody()->write($json);
    
    return $response;
}

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $data = ['error' => 'Ruta no encontrada'];
    $response->getBody()->write(json_encode($data));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
});

$app->run();
