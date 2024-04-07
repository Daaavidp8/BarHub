<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

$app->get('/get_tables/{id_owner}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id_owner');
    $sql = "SELECT * FROM DinnerTables WHERE id_restaurant = $id";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $owners = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($owners));
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

$app->post('/add_table/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');


    try {
        $sql = "SELECT COUNT(*) AS num_tables FROM DinnerTables WHERE id_restaurant = $ownerid";


        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);


        $stmt->execute();
        $next_table = $stmt->fetchColumn() + 1;

        $sql = "INSERT INTO DinnerTables (id_restaurant, table_number) VALUES (:id_restaurant, :table_number)";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':id_restaurant', $ownerid);
        $stmt->bindParam(':table_number', $next_table);

        $result = $stmt->execute();

        $db = null;

        $response->getBody()->write(json_encode($result));
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

$app->delete('/delete_table/{id_owner}', function (Request $request, Response $response, array $args) {
    $ownerid = $args["id_owner"];

    try {
        $sql = "SELECT COUNT(*) AS num_tables FROM DinnerTables WHERE id_restaurant = $ownerid";


        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);


        $stmt->execute();
        $tabletodelete = $stmt->fetchColumn();

        $sql = "DELETE FROM DinnerTables WHERE id_restaurant = $ownerid AND table_number = $tabletodelete";

        $stmt = $conn->prepare($sql);

        $result = $stmt->execute();

        $db = null;

        $response->getBody()->write(json_encode($result));
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