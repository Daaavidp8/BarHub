<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

$app->add(new RKA\Middleware\IpAddress);


// Devuelve el numero de un articulo concreto que hay en la cesta de una mesa
$app->get('/get_number_articles/{idRestaurante}/{numeroMesa}/{idArticulo}', function (Request $request, Response $response, $args) {
    $idRestaurant = $args['idRestaurante'];
    $numeroMesa = $args['numeroMesa'];
    $idArticulo = $args['idArticulo'];
    $sql = "SELECT COUNT(*) FROM ShoppingBasket WHERE id_restaurant = :idRestaurant AND table_number = :tableNumber AND id_article = :articulo  ";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $stmt->bindParam(':tableNumber', $numeroMesa, PDO::PARAM_INT);
        $stmt->bindParam(':articulo', $idArticulo, PDO::PARAM_INT);
        $stmt->execute();
        $numberArticles = $stmt->fetchColumn();
        $db = null;

        $response->getBody()->write(json_encode($numberArticles));
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


// Devuelve los articulos que hay en una mesa
$app->get('/resumeOrder/{idRestaurante}/{numeroMesa}', function (Request $request, Response $response, $args) {
    $idRestaurant = $args['idRestaurante'];
    $numeroMesa = $args['numeroMesa'];

    try {
        $db = new DB();
        $conn = $db->connect();

        // Obtener id_article y cantidad agrupada por artículo
        $sql = "SELECT id_article, COUNT(*) AS quantity 
                FROM OrderLines 
                WHERE id_state = 6 
                AND id_order IN (
                    SELECT id_order FROM Orders WHERE id_restaurant = :idRestaurant AND table_number = :tableNumber
                )
                GROUP BY id_article";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $stmt->bindParam(':tableNumber', $numeroMesa, PDO::PARAM_INT);
        $stmt->execute();
        $articlesWithQty = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($articlesWithQty) > 0) {
            // Extraer sólo los ids para la consulta de detalles
            $articleIds = array_column($articlesWithQty, 'id_article');
            $placeholders = rtrim(str_repeat('?,', count($articleIds)), ',');
            $sql = "SELECT * FROM Articles WHERE id_article IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            foreach ($articleIds as $index => $idArticle) {
                $stmt->bindValue(($index + 1), $idArticle, PDO::PARAM_INT);
            }
            $stmt->execute();
            $articlesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Combinar datos de artículos con cantidad
            $result = [];
            foreach ($articlesData as $article) {
                foreach ($articlesWithQty as $articleQty) {
                    if ($article['id_article'] == $articleQty['id_article']) {
                        $article['quantity'] = (int)$articleQty['quantity'];
                        $result[] = $article;
                        break;
                    }
                }
            }
        } else {
            $result = [];
        }

        $db = null;

        $response->getBody()->write(json_encode($result));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = ["message" => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('content-type', 'application/json')->withStatus(500);
    }
});




// Añade un articulo al pedido
$app->post('/create_row_basket/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');
    $data = $request->getParsedBody();
    $article = $data["id_article"];
    $numberTable = $data["number_table"];

    

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "INSERT INTO ShoppingBasket (`id_restaurant`, `table_number`, `id_article`) VALUES (:ownerid, :number_table, :idArticle)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $ownerid);
        $stmt->bindParam(':idArticle', $article);
        $stmt->bindParam(':number_table', $numberTable);
        $stmt->execute();

        $db = null;

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

// Añade el pedido de una mesa a los logs
$app->post('/order_log', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $ipAddress = $request->getAttribute('ip_address');
    $ownerName = $data["owner_name"];
    $article = $data["article"];
    $price = $data["price"];
    $numberTable = $data["number_table"];
    $codetable = $data["codetable"];
    $quantity = $data["quantity"];

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "INSERT INTO `Orders_log` (restaurant_name, ip_address, name_article, price_article, table_number, codetable, quantity) VALUES (:ownerName, :ipAddress, :articleName, :articlePrice, :number_table, :codetable, :quantity)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerName', $ownerName);
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->bindParam(':articleName', $article);
        $stmt->bindParam(':articlePrice', $price);
        $stmt->bindParam(':number_table', $numberTable);
        $stmt->bindParam(':codetable', $codetable);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();

        $db = null;

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



// Borra un articulo del pedido de una mesa concreta
$app->delete('/delete_article_basket/{idRestaurante}/{numeroMesa}/{idArticulo}', function (Request $request, Response $response, $args) {
    $idRestaurant = $args['idRestaurante'];
    $numeroMesa = $args['numeroMesa'];
    $idArticulo = $args['idArticulo'];

    $sql = "DELETE FROM ShoppingBasket WHERE id_restaurant = :idRestaurant AND table_number = :tableNumber AND id_article = :articulo LIMIT 1";

    try {
        $db = new DB();
        $conn = $db->connect();
        $conn->beginTransaction();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $stmt->bindParam(':tableNumber', $numeroMesa, PDO::PARAM_INT);
        $stmt->bindParam(':articulo', $idArticulo, PDO::PARAM_INT);
        $stmt->execute();
        $conn->commit();
        $db = null;
        $response->getBody()->write(json_encode(['message' => 'Artículo eliminado exitosamente']));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = ['message' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Borra todos los articulos del pedido de una mesa
$app->delete('/delete_basket/{idRestaurante}/{numeroMesa}', function (Request $request, Response $response, $args) {
    $idRestaurant = $args['idRestaurante'];
    $numeroMesa = $args['numeroMesa'];

    $sql = "DELETE FROM ShoppingBasket WHERE id_restaurant = :idRestaurant AND table_number = :tableNumber";

    try {
        $db = new DB();
        $conn = $db->connect();
        $conn->beginTransaction();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $stmt->bindParam(':tableNumber', $numeroMesa, PDO::PARAM_INT);
        $stmt->execute();
        $conn->commit();
        $db = null;
        $response->getBody()->write(json_encode(['message' => 'Cesta eliminada exitosamente']));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = ['message' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});
