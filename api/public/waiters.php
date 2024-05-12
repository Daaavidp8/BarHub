<?php

global $app;

require_once '../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;


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

$app->post('/get_qrTable/{id_owner}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id_owner');
    $data = $request->getParsedBody();
    $table = $data["number_table"];



    try {
        $db = new DB();
        $conn = $db->connect();


        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = :ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $id);
        $stmt->execute();
        $ownername = $stmt->fetchColumn();


        $sql = "SELECT codenumber FROM DinnerTables WHERE id_restaurant = :id AND table_number = :table";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':table', $table, PDO::PARAM_INT);
        $stmt->execute();
        $codenumber = $stmt->fetchColumn();


        $url = 'http://172.17.0.2/' . $ownername . "/pedido/" . $codenumber;
        $qrCode = QrCode::create($url)
            ->setSize(250)
            ->setMargin(40);

        $label = Label::create($codenumber);

        $writer = new PngWriter;
        $result = $writer->write($qrCode, null, $label);
        $imageBase64 = base64_encode($result->getString());

        $responseData = [
            'image' => $imageBase64
        ];

        $response->getBody()->write(json_encode($responseData));

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

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT codenumber AS num_tables FROM DinnerTables WHERE id_restaurant = $ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tableNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        do {
            $randomNumber = rand(0, 999999);
            $randomNumber = str_pad($randomNumber, 6, '0', STR_PAD_LEFT);
        } while (in_array($randomNumber, $tableNumbers));


        $sql = "SELECT COUNT(*) AS num_tables FROM DinnerTables WHERE id_restaurant = $ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $next_table = $stmt->fetchColumn() + 1;

        $sql = "INSERT INTO DinnerTables (id_restaurant, table_number, codenumber) VALUES (:id_restaurant, :table_number, :codenumber)";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':id_restaurant', $ownerid);
        $stmt->bindParam(':table_number', $next_table);
        $stmt->bindParam(':codenumber', $randomNumber);

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