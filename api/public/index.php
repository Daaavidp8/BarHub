<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use App\Models\DB;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'POST');
});



$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

include "./admin.php";
include "./owners.php";
include "./waiters.php";


$app->get('/get_token', function (Request $request, Response $response) {
    try {
        $token = bin2hex(random_bytes(16));
        $response->getBody()->write(json_encode($token));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->post('/get_sesion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $username = $data["username"];
    $password = $data["password"];

    $sql = "SELECT * FROM Users WHERE username = :username";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Obtener los datos del usuario
        $user = $stmt->fetch();

        if ($username && password_verify($password, $user['password'])) {
            $sql = "SELECT id_role FROM UsersRoles WHERE id_user = :id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $user['id_user']);
            $stmt->execute();

            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $data = array(
                "status" => true,
                "roles" => $roles,
                "restaurant" => $user['id_restaurant']
            );


        } else {
            $data = array(
                "status" => false,
                "message" => 'No se encontraron roles para el usuario.'
            );
        }





        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->run();
