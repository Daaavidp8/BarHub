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


$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

include "./admin.php";
include "./owners.php";
include "./waiters.php";

$app->post('/get_sesion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    // Filtrar y validar entrada
    $username = filter_var($data["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($data["password"], FILTER_SANITIZE_STRING);

    $sql = "SELECT * FROM Users WHERE username = :username";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Obtener los datos del usuario
        $user = $stmt->fetch();

        // Verificar la contraseña
        if ($user && password_verify($password, $user['password'])) {
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
                "message" => 'Credenciales inválidas.'
            );
        }

        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => "Error en la base de datos"
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->run();

