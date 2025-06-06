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

        // Agrupar por id_article e id_order, contar cuántas filas hay para cada combinación
        $sql = "SELECT id_article, id_order, MAX(id_order_line) as id_order_line, COUNT(*) AS quantity 
                FROM OrderLines 
                WHERE id_state = 6 
                AND id_order IN (
                    SELECT id_order FROM Orders WHERE id_restaurant = :idRestaurant AND table_number = :tableNumber
                )
                GROUP BY id_article, id_order";
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

            // Combinar datos de artículos con cantidad, id_order_line e id_order
            $result = [];
            foreach ($articlesWithQty as $articleQty) {
                foreach ($articlesData as $article) {
                    if ($article['id_article'] == $articleQty['id_article']) {
                        $articleWithLine = $article;
                        $articleWithLine['quantity'] = (int)$articleQty['quantity'];
                        $articleWithLine['id_order_line'] = $articleQty['id_order_line'];
                        $articleWithLine['id_order'] = $articleQty['id_order'];
                        $result[] = $articleWithLine;
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
$app->post('/create_row_basket/{id_owner}', function (Request $request, Response $response, $args) {
    $ownerid = $args['id_owner'];
    $data = $request->getParsedBody();
    $article = $data["id_article"];
    $numberTable = $data["number_table"];

    try {
        $db = new DB();
        $conn = $db->connect();

        // Buscar la última orden con líneas activas (id_state 5 o 6)
        $sqlOrder = "
            SELECT DISTINCT o.id_order
            FROM Orders o
            JOIN OrderLines ol ON ol.id_order = o.id_order
            WHERE o.id_restaurant = :ownerid
            AND o.table_number = :number_table
            AND ol.id_state IN (5,6)
            ORDER BY o.id_order DESC
            LIMIT 1
        ";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->bindParam(':ownerid', $ownerid, PDO::PARAM_INT);
        $stmtOrder->bindParam(':number_table', $numberTable, PDO::PARAM_INT);
        $stmtOrder->execute();
        $orderRow = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if (!$orderRow || !isset($orderRow['id_order'])) {
            // No existe orden activa, crear nueva
            $sqlInsertOrder = "INSERT INTO Orders (id_restaurant, restaurant_name, order_date, client_ipaddress, table_number, total) VALUES (:ownerid, '', NOW(), '', :number_table, 0)";
            $stmtInsertOrder = $conn->prepare($sqlInsertOrder);
            $stmtInsertOrder->bindParam(':ownerid', $ownerid, PDO::PARAM_INT);
            $stmtInsertOrder->bindParam(':number_table', $numberTable, PDO::PARAM_INT);
            $stmtInsertOrder->execute();
            $idOrder = $conn->lastInsertId();
        } else {
            $idOrder = $orderRow['id_order'];
        }

        // Obtener article_name y article_price desde Articles
        $sqlArticle = "SELECT name, price FROM Articles WHERE id_article = :id_article LIMIT 1";
        $stmtArticle = $conn->prepare($sqlArticle);
        $stmtArticle->bindParam(':id_article', $article, PDO::PARAM_INT);
        $stmtArticle->execute();
        $articleData = $stmtArticle->fetch(PDO::FETCH_ASSOC);

        if (!$articleData) {
            $db = null;
            $response->getBody()->write(json_encode(['message' => 'No se encontró el artículo']));
            return $response->withHeader('content-type', 'application/json')->withStatus(400);
        }

        $articleName = $articleData['name'];
        $articlePrice = $articleData['price'];

        // Insertar línea en OrderLines
        $sqlInsert = "INSERT INTO OrderLines (id_article, article_name, article_price, id_state, id_order) 
                      VALUES (:id_article, :article_name, :article_price, 6, :id_order)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bindParam(':id_article', $article, PDO::PARAM_INT);
        $stmtInsert->bindParam(':article_name', $articleName);
        $stmtInsert->bindParam(':article_price', $articlePrice);
        $stmtInsert->bindParam(':id_order', $idOrder, PDO::PARAM_INT);
        $stmtInsert->execute();

        $db = null;

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array("message" => $e->getMessage());

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});




// Añade un articulo al pedido
$app->put('/set_order_to_prep/{id_order}', function (Request $request, Response $response, $args) {
    $idOrder = $args['id_order'];

    try {
        $db = new DB();
        $conn = $db->connect();

        // Cambiar el estado de todas las order_lines con ese id_order y id_state = 6 a id_state = 5
        $sqlUpdate = "UPDATE OrderLines SET id_state = 5 WHERE id_order = :id_order AND id_state = 6";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id_order', $idOrder, PDO::PARAM_INT);
        $stmtUpdate->execute();

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
// Borra la última fila de OrderLines con ese id_order, id_article y id_state = 6
$app->delete('/delete_article_basket/{idOrder}/{idArticle}', function (Request $request, Response $response, $args) {
    $idOrder = $args['idOrder'];
    $idArticle = $args['idArticle'];

    // Buscar la última fila que cumpla las condiciones
    $sqlSelect = "SELECT id_order_line FROM OrderLines WHERE id_order = :idOrder AND id_article = :idArticle AND id_state = 6 ORDER BY id_order_line DESC LIMIT 1";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bindParam(':idOrder', $idOrder, PDO::PARAM_INT);
        $stmtSelect->bindParam(':idArticle', $idArticle, PDO::PARAM_INT);
        $stmtSelect->execute();
        $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($row['id_order_line'])) {
            $idOrderLine = $row['id_order_line'];
            $sqlDelete = "DELETE FROM OrderLines WHERE id_order_line = :idOrderLine";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bindParam(':idOrderLine', $idOrderLine, PDO::PARAM_INT);
            $stmtDelete->execute();

            $db = null;
            $response->getBody()->write(json_encode(['message' => 'Artículo eliminado exitosamente']));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $db = null;
            $response->getBody()->write(json_encode(['message' => 'No se encontró la fila a eliminar']));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
    } catch (PDOException $e) {
        $error = ['message' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});
