<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;





$app->put('/set_state_orderLine/{idOrderLine}', function (Request $request, Response $response, $args) {
    $idOrderLine = $args['idOrderLine'];
    
    // Get request body data - parse JSON input
    $input = $request->getBody()->getContents();
    $data = json_decode($input, true);
    
    // Log the received data to error log
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => '/set_state_orderLine/' . $idOrderLine,
        'method' => 'POST',
        'request_body' => $data,
        'request_args' => $args,
        'raw_input' => $input
    ];
    
    // Output to error log
    error_log('SET_STATE_ORDERLINE RECEIVED DATA: ' . json_encode($logData, JSON_PRETTY_PRINT));
    
    // Check if id_state is provided
    if (!isset($data['id_state'])) {
        $error = [
            "message" => "id_state is required"
        ];
        
        // Log the error
        error_log('ERROR: id_state is required for order line ID: ' . $idOrderLine);
        
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
    
    $idState = $data['id_state'];
    
    try {
        $db = new DB();
        $conn = $db->connect();
        
        // Update the state of the order line
        $sql = "UPDATE OrderLines SET id_state = :idState WHERE id_order_line = :idOrderLine";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idState', $idState, PDO::PARAM_INT);
        $stmt->bindParam(':idOrderLine', $idOrderLine, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Get the updated order line
            $getOrderLineSql = "SELECT * FROM OrderLines WHERE id_order_line = :idOrderLine";
            $getOrderLineStmt = $conn->prepare($getOrderLineSql);
            $getOrderLineStmt->bindParam(':idOrderLine', $idOrderLine, PDO::PARAM_INT);
            $getOrderLineStmt->execute();
            $orderLine = $getOrderLineStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orderLine) {
                $error = [
                    "message" => "Order line not found after update"
                ];
                
                $response->getBody()->write(json_encode($error));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(404);
            }
            
            // Get state information
            $stateSql = "SELECT * FROM States WHERE id_state = :idState";
            $stateStmt = $conn->prepare($stateSql);
            $stateStmt->bindParam(':idState', $idState, PDO::PARAM_INT);
            $stateStmt->execute();
            $state = $stateStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get article information
            $articleSql = "SELECT * FROM Articles WHERE id_article = :idArticle";
            $articleStmt = $conn->prepare($articleSql);
            $articleStmt->bindParam(':idArticle', $orderLine['id_article'], PDO::PARAM_INT);
            $articleStmt->execute();
            $article = $articleStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get section information if article exists
            $section = null;
            if ($article && isset($article['id_section'])) {
                $sectionSql = "SELECT * FROM Sections WHERE id_section = :idSection";
                $sectionStmt = $conn->prepare($sectionSql);
                $sectionStmt->bindParam(':idSection', $article['id_section'], PDO::PARAM_INT);
                $sectionStmt->execute();
                $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Get order information
            $orderSql = "SELECT o.*, r.* FROM Orders o 
                        JOIN Restaurants r ON o.id_restaurant = r.id_restaurant 
                        WHERE o.id_order = :idOrder";
            $orderStmt = $conn->prepare($orderSql);
            $orderStmt->bindParam(':idOrder', $orderLine['id_order'], PDO::PARAM_INT);
            $orderStmt->execute();
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
            
            // Build restaurant data
            $restaurantData = [];
            if ($order) {
                $restaurantData = [
                    'id_restaurant' => $order['id_restaurant'],
                    'name' => $order['name'],
                    'cif' => $order['cif'],
                    'contactEmail' => $order['contactEmail'],
                    'contactPhone' => $order['contactPhone']
                ];
            }
            
            // Build section data
            $sectionData = [];
            if ($section) {
                $sectionData = [
                    'id_section' => $section['id_section'],
                    'name' => $section['name'],
                    'restaurant' => $restaurantData
                ];
            }
            
            // Build article data
            $articleData = [];
            if ($article) {
                $articleData = [
                    'id_article' => $article['id_article'],
                    'name' => $article['name'],
                    'price' => $article['price'],
                    'section' => $sectionData
                ];
            }
            
            // Build order data
            $orderData = [];
            if ($order) {
                $orderData = [
                    'id_order' => $order['id_order'],
                    'id_restaurant' => $order['id_restaurant'],
                    'restaurant_name' => $order['restaurant_name'],
                    'order_date' => $order['order_date'],
                    'client_ipaddress' => $order['client_ipaddress'],
                    'table_number' => $order['table_number'],
                    'total' => $order['total'],
                    'restaurant' => $restaurantData
                ];
            }
            
            // Build the response
            $result = [
                'id_order_line' => $orderLine['id_order_line'],
                'article' => $articleData,
                'article_name' => $orderLine['article_name'],
                'article_price' => $orderLine['article_price'],
                'state' => $state,
                'order' => $orderData
            ];
            
            $db = null;
            
            $response->getBody()->write(json_encode($result));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } else {
            $error = [
                "message" => "Failed to update order line state"
            ];
            
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        // Log the detailed error
        error_log("DATABASE ERROR: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


// Modified endpoint to get pending order lines with multiple roles and specific state
$app->get('/get_pending_order_lines/{idRestaurante}/{idRoles}/{idState}', function (Request $request, Response $response, $args) {

    $idRestaurant = $args['idRestaurante'];
    $idRolesString = $args['idRoles'];
    $idState = $args['idState'];
    
    // Convert comma-separated roles to array
    $idRoles = explode(',', $idRolesString);
    
    // Log the request parameters
    error_log("Processing request with idRoles: " . json_encode($idRoles) . ", idState: $idState, idRestaurant: $idRestaurant");
 
    // First, get all orders for the specific restaurant
    $orderSql = "SELECT * FROM Orders WHERE id_restaurant = :idRestaurant";

    try {
        $db = new DB();
        $conn = $db->connect();
        
        // Fetch all orders for the restaurant
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $orderStmt->execute();
        $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all order IDs
        $orderIds = array_column($orders, 'id_order');
        
        if (empty($orderIds)) {
            // No orders found for this restaurant
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
        
        // Get sections associated with any of the specified roles
        $placeholders = implode(',', array_fill(0, count($idRoles), '?'));
        $sectionSql = "SELECT DISTINCT s.* FROM Sections s 
                      JOIN SectionsRoles sr ON s.id_section = sr.id_section 
                      WHERE s.id_restaurant = ? AND sr.id_role IN ($placeholders)";
        
        $sectionStmt = $conn->prepare($sectionSql);
        
        // Bind parameters - first the restaurant ID, then all role IDs
        $sectionStmt->bindValue(1, $idRestaurant, PDO::PARAM_INT);
        foreach ($idRoles as $index => $idRole) {
            $sectionStmt->bindValue($index + 2, $idRole, PDO::PARAM_INT);
        }
        
        $sectionStmt->execute();
        $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log the sections found
        error_log("Sections found: " . count($sections) . " for roles: " . json_encode($idRoles) . " in restaurant ID: $idRestaurant");
        
        // Get section IDs
        $sectionIds = array_column($sections, 'id_section');
        
        if (empty($sectionIds)) {
            // No sections found for these roles in this restaurant
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
        
        // Get articles in these sections
        $articleSql = "SELECT * FROM Articles WHERE id_section IN (" . implode(',', array_fill(0, count($sectionIds), '?')) . ")";
        $articleStmt = $conn->prepare($articleSql);
        foreach ($sectionIds as $index => $id) {
            $articleStmt->bindValue($index + 1, $id);
        }
        $articleStmt->execute();
        $articles = $articleStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get article IDs
        $articleIds = array_column($articles, 'id_article');
        
        if (empty($articleIds)) {
            // No articles found in these sections
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
        
        // Get order lines for these orders and articles with the specified state
        $orderLinesSql = "SELECT ol.* FROM OrderLines ol 
                         WHERE ol.id_order IN (" . implode(',', array_fill(0, count($orderIds), '?')) . ")
                         AND ol.id_article IN (" . implode(',', array_fill(0, count($articleIds), '?')) . ")
                         AND ol.id_state = ?";
        $orderLinesStmt = $conn->prepare($orderLinesSql);
        
        // Bind order IDs
        $paramIndex = 1;
        foreach ($orderIds as $id) {
            $orderLinesStmt->bindValue($paramIndex++, $id);
        }
        
        // Bind article IDs
        foreach ($articleIds as $id) {
            $orderLinesStmt->bindValue($paramIndex++, $id);
        }
        
        // Bind state ID
        $orderLinesStmt->bindValue($paramIndex, $idState, PDO::PARAM_INT);
        
        $orderLinesStmt->execute();
        $orderLines = $orderLinesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log the order lines found
        error_log("Order lines found: " . count($orderLines) . " with state ID: $idState");
        
        if (empty($orderLines)) {
            // No order lines found with the specified state
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        }
        
        // Get all state IDs from order lines (should be just the one we filtered by)
        $stateIds = array_unique(array_column($orderLines, 'id_state'));
        
        // Fetch all states
        if (!empty($stateIds)) {
            $stateSql = "SELECT * FROM States WHERE id_state IN (" . implode(',', array_fill(0, count($stateIds), '?')) . ")";
            $stateStmt = $conn->prepare($stateSql);
            foreach ($stateIds as $index => $id) {
                $stateStmt->bindValue($index + 1, $id);
            }
            $stateStmt->execute();
            $states = $stateStmt->fetchAll(PDO::FETCH_ASSOC);
            $statesMap = [];
            foreach ($states as $state) {
                $statesMap[$state['id_state']] = $state;
            }
        } else {
            $statesMap = [];
        }
        
        // Fetch restaurant info
        $restaurantSql = "SELECT * FROM Restaurants WHERE id_restaurant = :idRestaurant";
        $restaurantStmt = $conn->prepare($restaurantSql);
        $restaurantStmt->bindParam(':idRestaurant', $idRestaurant, PDO::PARAM_INT);
        $restaurantStmt->execute();
        $restaurant = $restaurantStmt->fetch(PDO::FETCH_ASSOC);
        
        // Create maps for easy lookup
        $articlesMap = [];
        foreach ($articles as $article) {
            $articlesMap[$article['id_article']] = $article;
        }
        
        $sectionsMap = [];
        foreach ($sections as $section) {
            $sectionsMap[$section['id_section']] = $section;
        }
        
        $ordersMap = [];
        foreach ($orders as $order) {
            $ordersMap[$order['id_order']] = $order;
        }
        
        // Group order lines by order ID
        $orderLinesGrouped = [];
        foreach ($orderLines as $line) {
            $orderLinesGrouped[$line['id_order']][] = $line;
        }
        
        // Build the final response
        $formattedData = [];
        foreach ($orders as $order) {
            $orderLines = $orderLinesGrouped[$order['id_order']] ?? [];
            
            if (empty($orderLines)) {
                continue; // Skip orders with no matching order lines
            }
            
            $formattedOrderLines = [];
            foreach ($orderLines as $line) {
                $article = $articlesMap[$line['id_article']] ?? [];
                
                // Create section with restaurant data
                $sectionData = [];
                if (!empty($article) && isset($article['id_section']) && isset($sectionsMap[$article['id_section']])) {
                    $sectionData = [
                        'id_section' => $sectionsMap[$article['id_section']]['id_section'],
                        'name' => $sectionsMap[$article['id_section']]['name'],
                        'restaurant' => [
                            'id_restaurant' => $restaurant['id_restaurant'],
                            'name' => $restaurant['name'],
                            'cif' => $restaurant['cif'],
                            'contactEmail' => $restaurant['contactEmail'],
                            'contactPhone' => $restaurant['contactPhone']
                        ]
                    ];
                }
                
                // Restructure article to include section
                $restructuredArticle = [];
                if (!empty($article)) {
                    // Generate image path based on restaurant and article names
                    $restaurantName = $restaurant['name'] ?? '';
                    $articleName = $article['name'] ?? '';
                    $imagePath = "http://192.168.1.146:41063/owners/{$restaurantName}/img/articles/{$articleName}.png";
                    
                    $restructuredArticle = [
                        'id_article' => $article['id_article'],
                        'name' => $article['name'],
                        'price' => $article['price'],
                        'image' => $imagePath,
                        'section' => $sectionData
                    ];
                }
                
                $formattedOrderLines[] = [
                    'id_order_line' => $line['id_order_line'],
                    'article' => $restructuredArticle,
                    'article_name' => $line['article_name'],
                    'article_price' => $line['article_price'],
                    'state' => $line['id_state'] ?? []
                ];
            }
            
            // Create order object with nested order lines
            $formattedData[] = [
                'id_order' => $order['id_order'],
                'id_restaurant' => $order['id_restaurant'],
                'restaurant_name' => $order['restaurant_name'],
                'order_date' => $order['order_date'],
                'client_ipaddress' => $order['client_ipaddress'],
                'table_number' => $order['table_number'],
                'total' => $order['total'],
                'restaurant' => [
                    'id_restaurant' => $restaurant['id_restaurant'],
                    'name' => $restaurant['name'],
                    'cif' => $restaurant['cif'],
                    'contactEmail' => $restaurant['contactEmail'],
                    'contactPhone' => $restaurant['contactPhone']
                ],
                'order_lines' => $formattedOrderLines
            ];
        }
        
        $db = null;

        $response->getBody()->write(json_encode($formattedData));
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



