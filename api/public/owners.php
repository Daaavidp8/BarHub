<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

// Devuelve la secciones de un restaurante en especifico
$app->get('/get_sections/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    
    try {
        $db = new DB();
        $conn = $db->connect();
        
        // Get restaurant name first
        $sqlRestaurant = "SELECT name FROM Restaurants WHERE id_restaurant = :id";
        $stmtRestaurant = $conn->prepare($sqlRestaurant);
        $stmtRestaurant->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtRestaurant->execute();
        $restaurantName = $stmtRestaurant->fetchColumn();
        
        // Get all sections for this restaurant
        $sqlSections = "SELECT * FROM Sections WHERE id_restaurant = :id";
        $stmtSections = $conn->prepare($sqlSections);
        $stmtSections->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSections->execute();
        $sections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);
        
        // For each section, get its articles and image
        $result = [];
        foreach ($sections as $section) {
            $sectionId = $section['id_section'];
            $sectionName = $section['name'];
            
            // Get section image and encode it in base64
            $imageRelativePath = "/owners/{$restaurantName}/img/sections/{$sectionName}.png";
            $imageFullPath = "." . $imageRelativePath; // For file_exists check
            
            if (file_exists($imageFullPath)) {
                // Use URL instead of base64
                $section['image'] = BASE_URL . $imageRelativePath;
            } else {
                $section['image'] = "";
            }
            
            // Get articles for this section
            $sqlArticles = "SELECT * FROM Articles WHERE id_section = :sectionId";
            $stmtArticles = $conn->prepare($sqlArticles);
            $stmtArticles->bindParam(':sectionId', $sectionId, PDO::PARAM_INT);
            $stmtArticles->execute();
            $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
            
            // Process each article to include its image
            foreach ($articles as &$article) {
                $articleName = $article['name'];
                $articleRelativePath = "/owners/{$restaurantName}/img/articles/{$articleName}.png";
                $articleFullPath = "." . $articleRelativePath; // For file_exists check
                
                if (file_exists($articleFullPath)) {
                    // Use URL instead of base64
                    $article['image'] = BASE_URL . $articleRelativePath;
                } else {
                    $article['image'] = "";
                    error_log("Article image not found: {$articleFullPath}");
                }
            }
            
            // Add articles to section
            $section['articles'] = $articles;
            $result[] = $section;
        }
        
        $db = null;
        
        // Añadimos un log para registrar la información de las secciones
        error_log("Secciones recuperadas para el restaurante ID: " . $id . " - Total de secciones: " . count($result));
        
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

// Devuelve una sección especifica
$app->get('/get_section/{id_section}', function (Request $request, Response $response) {
    $sectionid = $request->getAttribute('id_section');
    $sql = "SELECT * FROM Sections WHERE id_section = :sectionid";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sectionid', $sectionid, PDO::PARAM_INT);
        $stmt->execute();
        $owner = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($owner));
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

// Devuelve los articulos de una sección especifica
$app->get('/get_articles/{id_section}', function (Request $request, Response $response) {
    $sectionid = $request->getAttribute('id_section');
    $sql = "SELECT * FROM Articles WHERE id_section = $sectionid";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $owner = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($owner));
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


// Devuelve los trabajadores de un restaurante
$app->get('/get_workers/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');

    try {
        $db = new DB();
        $conn = $db->connect();
        
        // Get all workers for this restaurant
        $sql = "SELECT * FROM Users WHERE id_restaurant = :ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $ownerid, PDO::PARAM_INT);
        $stmt->execute();
        $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For each worker, get their roles
        foreach ($workers as &$worker) {
            $userId = $worker['id_user'];
            
            // Get roles for this worker
            $sqlRoles = "SELECT id_role FROM UsersRoles WHERE id_user = :userId";
            $stmtRoles = $conn->prepare($sqlRoles);
            $stmtRoles->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtRoles->execute();
            
            // Fetch all roles and convert to integers
            $roles = [];
            while ($role = $stmtRoles->fetchColumn()) {
                $roles[] = intval($role);
            }
            
            // Add roles array to worker data
            $worker['roles'] = $roles;
            
            // Remove password from response for security
            unset($worker['password']);
        }
        
        $db = null;

        $response->getBody()->write(json_encode($workers));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        error_log("Database error in GET_WORKERS: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Devuelve un trabajador en concreto
$app->get('/get_worker/{id_user}', function (Request $request, Response $response) {
    $workerid = $request->getAttribute('id_user');


    try {
        $db = new DB();
        $conn = $db->connect();
        $sql = "SELECT * FROM Users WHERE id_user = $workerid";
        $stmt = $conn->query($sql);
        $dataUser = $stmt->fetchAll(PDO::FETCH_OBJ);
        $sql = "SELECT id_role FROM UsersRoles WHERE id_user = $workerid";
        $stmt = $conn->query($sql);
        $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $data = array(
            "userData" => $dataUser,
            "roles" => $roles
        );

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

// Crea una seccion en un restaurante especifico
$app->post('/create_section/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');
    $contentType = $request->getHeaderLine('Content-Type');
    
    // Check if the request is JSON or multipart form data
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON request
        $data = $request->getParsedBody();
        if (!$data) {
            $data = json_decode($request->getBody()->getContents(), true);
        }
        
        if (!isset($data["section_name"])) {
            throw new Exception("El campo 'section_name' es requerido");
        }
        
        $name = $data["section_name"];
        $imageBase64 = isset($data["section_img"]) ? $data["section_img"] : null;
        
        // Log received data for debugging
        error_log("Received section name: " . $name);
        error_log("Received base64 image: " . (empty($imageBase64) ? "No" : "Yes, length: " . strlen($imageBase64)));
    } else {
        // Handle multipart form data
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        
        if (!isset($data["section_name"])) {
            throw new Exception("El campo 'section_name' es requerido");
        }
        
        $name = $data["section_name"];
        $imageBase64 = null;
    }

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = :ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $ownerid);
        $result = $stmt->execute();

        if (!$result || !($ownername = $stmt->fetchColumn())) {
            throw new Exception("No se encontró el restaurante con ID: " . $ownerid);
        }

        // Create full directory path
        $ruta = "./owners/" . $ownername;
        $directorio = $ruta . "/img/sections/";
        
        // Ensure all parent directories exist
        if (!is_dir($ruta)) {
            if (!mkdir($ruta, 0777, true)) {
                throw new Exception("No se pudo crear el directorio base: " . $ruta);
            }
        }
        
        if (!is_dir($ruta . "/img")) {
            if (!mkdir($ruta . "/img", 0777, true)) {
                throw new Exception("No se pudo crear el directorio de imágenes: " . $ruta . "/img");
            }
        }
        
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0777, true)) {
                throw new Exception("No se pudo crear el directorio de secciones: " . $directorio);
            }
        }
        
        $nombreArchivo = $name . '.png';
        $rutaArchivo = $directorio . $nombreArchivo;
        
        error_log("Saving image to: " . $rutaArchivo);

        // Handle file upload or base64 image
        if (isset($uploadedFiles['section_img']) && $uploadedFiles['section_img']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $uploadedFiles['section_img'];
            if ($uploadedFile->getClientMediaType() !== 'image/png') {
                throw new Exception("El archivo subido no tiene extensión PNG.");
            }
            $uploadedFile->moveTo($rutaArchivo);
            error_log("Image uploaded successfully via multipart form");
        } elseif ($imageBase64) {
            // Handle base64 encoded image
            // Remove data URI prefix if present
            if (strpos($imageBase64, 'data:image/png;base64,') === 0) {
                $imageBase64 = substr($imageBase64, strlen('data:image/png;base64,'));
            } elseif (strpos($imageBase64, 'data:image/jpeg;base64,') === 0) {
                $imageBase64 = substr($imageBase64, strlen('data:image/jpeg;base64,'));
            }
            
            // Ensure the base64 string is clean (remove any whitespace)
            $imageBase64 = trim($imageBase64);
            
            // Decode the base64 data
            $imageData = base64_decode($imageBase64, true);
            if ($imageData === false) {
                throw new Exception("Error al decodificar la imagen en base64. Formato inválido.");
            }
            
            // Save the image file
            $bytesWritten = file_put_contents($rutaArchivo, $imageData);
            if ($bytesWritten === false) {
                throw new Exception("Error al guardar la imagen: " . $rutaArchivo);
            }
            
            error_log("Image saved successfully from base64. Bytes written: " . $bytesWritten);
        } else {
            error_log("No image provided");
        }

        // Insert section into database
        $sql = "INSERT INTO Sections (name, id_restaurant) VALUES (:name, :idowner)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':idowner', $ownerid);

        $result = $stmt->execute();
        $sectionId = $conn->lastInsertId();
        
        // Create the complete section object to return
        $section = [
            "id" => intval($sectionId),
            "name" => $name,
            "id_restaurant" => intval($ownerid),
            "image" => null,
            "articles" => []
        ];
        
        // Add the image to the response if it exists
        if (file_exists($rutaArchivo)) {
            $imageData = file_get_contents($rutaArchivo);
            $section['image'] = "data:image/png;base64," . base64_encode($imageData);
        }

        $db = null;

        // Return the complete section data
        $response->getBody()->write(json_encode($section));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});


// Crea un articulo en una sección determinada
$app->post('/create_article/{id_section}', function (Request $request, Response $response) {
    $sectionid = $request->getAttribute('id_section');
    $contentType = $request->getHeaderLine('Content-Type');
    
    // Check if the request is JSON or multipart form data
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON request
        $data = $request->getParsedBody();
        if (!$data) {
            $data = json_decode($request->getBody()->getContents(), true);
        }
        
        if (!isset($data["article_name"]) || !isset($data["article_price"])) {
            throw new Exception("Los campos 'article_name' y 'article_price' son requeridos");
        }
        
        $name = $data["article_name"];
        $price = $data["article_price"];
        $imageBase64 = isset($data["article_img"]) ? $data["article_img"] : null;
        
        // Log received data for debugging
        error_log("Received article name: " . $name);
        error_log("Received article price: " . $price);
        error_log("Received base64 image: " . (empty($imageBase64) ? "No" : "Yes, length: " . strlen($imageBase64)));
    } else {
        // Handle multipart form data
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        
        if (!isset($data["article_name"]) || !isset($data["article_price"])) {
            throw new Exception("Los campos 'article_name' y 'article_price' son requeridos");
        }
        
        $name = $data["article_name"];
        $price = $data["article_price"];
        $imageBase64 = null;
    }

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT id_restaurant FROM Sections WHERE id_section = :sectionid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sectionid', $sectionid);
        $result = $stmt->execute();

        if ($result) {
            $ownerid = $stmt->fetchColumn();
        }

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = :ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $ownerid);
        $result = $stmt->execute();

        if ($result) {
            $ownername = $stmt->fetchColumn();
        }

        $ruta = "./owners/" . $ownername;
        $directorio = $ruta . "/img/articles/";
        
        // Ensure directory exists
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0777, true)) {
                throw new Exception("No se pudo crear el directorio de artículos: " . $directorio);
            }
        }
        
        $nombreArchivo = $name . '.png';
        $rutaArchivo = $directorio . $nombreArchivo;

        // Handle file upload or base64 image
        $uploadedFile = isset($uploadedFiles['article_img']) ? $uploadedFiles['article_img'] : null;
        if ($uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            if ($uploadedFile->getClientMediaType() !== 'image/png') {
                throw new Exception("El archivo subido no tiene extensión PNG.");
            }
            $uploadedFile->moveTo($rutaArchivo);
            error_log("Image uploaded successfully via multipart form");
        } elseif ($imageBase64) {
            // Handle base64 encoded image
            // Remove data URI prefix if present
            if (strpos($imageBase64, 'data:image/png;base64,') === 0) {
                $imageBase64 = substr($imageBase64, strlen('data:image/png;base64,'));
            } elseif (strpos($imageBase64, 'data:image/jpeg;base64,') === 0) {
                $imageBase64 = substr($imageBase64, strlen('data:image/jpeg;base64,'));
            }
            
            // Ensure the base64 string is clean (remove any whitespace)
            $imageBase64 = trim($imageBase64);
            
            // Decode the base64 data
            $imageData = base64_decode($imageBase64, true);
            if ($imageData === false) {
                throw new Exception("Error al decodificar la imagen en base64. Formato inválido.");
            }
            
            // Save the image file
            $bytesWritten = file_put_contents($rutaArchivo, $imageData);
            if ($bytesWritten === false) {
                throw new Exception("Error al guardar la imagen: " . $rutaArchivo);
            }
            
            error_log("Image saved successfully from base64. Bytes written: " . $bytesWritten);
        }

        $sql = "INSERT INTO Articles (name, price, id_section) VALUES (:name, :price, :idsection)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':idsection', $sectionid);
        $result = $stmt->execute();
        $articleId = $conn->lastInsertId();
        
        // Create the complete article object to return
        $article = [
            "id" => intval($articleId),
            "name" => $name,
            "price" => floatval($price),
            "id_section" => intval($sectionid),
            "image" => null
        ];
        
        // Add the image to the response if it exists
        if (file_exists($rutaArchivo)) {
            $imageData = file_get_contents($rutaArchivo);
            $article['image'] = "data:image/png;base64," . base64_encode($imageData);
        }

        $db = null;

        $response->getBody()->write(json_encode($article));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        error_log("General error: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});


// Crea trabajadores en un restaurante en concreto
// Crea trabajadores en un restaurante en concreto
$app->post('/create_worker/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');
    
    // Log para ver qué tipo de contenido se está recibiendo
    $contentType = $request->getHeaderLine('Content-Type');
    error_log("CREATE_WORKER - Content-Type: " . $contentType);
    
    // Log del cuerpo de la solicitud
    $requestBody = $request->getBody()->getContents();
    error_log("CREATE_WORKER - Request Body: " . $requestBody);
    
    // Intentar obtener los datos según el tipo de contenido
    if (strpos($contentType, 'application/json') !== false) {
        // Para JSON
        $data = json_decode($requestBody, true);
        error_log("CREATE_WORKER - JSON data: " . json_encode($data));
    } else {
        // Para form data
        $data = $request->getParsedBody();
        error_log("CREATE_WORKER - Form data: " . json_encode($data));
    }
    
    // Verificar si los datos están presentes
    if (!$data) {
        error_log("CREATE_WORKER - No se recibieron datos");
        throw new Exception("No se recibieron datos en la solicitud");
    }
    
    // Verificar cada campo individualmente
    if (!isset($data["worker_name"])) {
        error_log("CREATE_WORKER - Falta worker_name");
        throw new Exception("El campo 'worker_name' es requerido");
    }
    
    if (!isset($data["worker_username"])) {
        error_log("CREATE_WORKER - Falta worker_username");
        throw new Exception("El campo 'worker_username' es requerido");
    }
    
    if (!isset($data["worker_password"])) {
        error_log("CREATE_WORKER - Falta worker_password");
        throw new Exception("El campo 'worker_password' es requerido");
    }
    
    if (!isset($data["worker_roles"])) {
        error_log("CREATE_WORKER - Falta worker_roles");
        throw new Exception("El campo 'worker_roles' es requerido");
    }
    
    $name = $data["worker_name"];
    $username = $data["worker_username"];
    $password = password_hash($data["worker_password"], PASSWORD_DEFAULT);
    $roles = $data["worker_roles"];
    
    // Log de los datos procesados
    error_log("CREATE_WORKER - Datos procesados: name={$name}, username={$username}, roles={$roles}");

    try {
        $db = new DB();
        $conn = $db->connect();

        // Verificar si el nombre de usuario ya existe
        $sql = "SELECT COUNT(*) FROM Users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            throw new Exception("El nombre de usuario '$username' ya existe.");
        }

        // Insertar el nuevo usuario
        $sql = "INSERT INTO Users (name, username, password, id_restaurant) VALUES (:name, :username, :password, :idowner)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':idowner', $ownerid);
        $stmt->execute();

        // Obtener el ID del usuario recién insertado
        $userid = $conn->lastInsertId();
        error_log("CREATE_WORKER - Usuario creado con ID: {$userid}");

        // Insertar roles del usuario
        if (is_array($roles)) {
            error_log("CREATE_WORKER - Roles es un array");
            foreach ($roles as $role) {
                $sql = "INSERT INTO UsersRoles (id_user, id_role) VALUES (:id_user, :id_role)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_user', $userid);
                $stmt->bindParam(':id_role', $role);
                $stmt->execute();
                error_log("CREATE_WORKER - Rol {$role} asignado al usuario {$userid}");
            }
        } else {
            error_log("CREATE_WORKER - Roles no es un array, es: " . gettype($roles));
            $rolesArray = explode(',', $roles);
            foreach ($rolesArray as $role) {
                if (trim($role) === '') continue;
                $sql = "INSERT INTO UsersRoles (id_user, id_role) VALUES (:id_user, :id_role)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_user', $userid);
                $stmt->bindParam(':id_role', $role);
                $stmt->execute();
                error_log("CREATE_WORKER - Rol {$role} asignado al usuario {$userid}");
            }
        }

        $sql = "SELECT * FROM Users WHERE id_user = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get the roles for this user
        $sqlRoles = "SELECT id_role FROM UsersRoles WHERE id_user = :userid";
        $stmtRoles = $conn->prepare($sqlRoles);
        $stmtRoles->bindParam(':userid', $userid);
        $stmtRoles->execute();
        
        // Fetch all roles and convert to integers
        $roles = [];
        while ($role = $stmtRoles->fetchColumn()) {
            $roles[] = intval($role);
        }
        
        // Add roles to user data
        $userData['roles'] = $roles;
        
        // Remove password from response for security
        unset($userData['password']);
        
        // Change id_user to id in the response
        $userData['id'] = $userData['id_user'];
        unset($userData['id_user']);
        
        $response->getBody()->write(json_encode($userData));
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        error_log("CREATE_WORKER - Error: " . $e->getMessage());
        $error = array("error" => $e->getMessage());
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('content-type', 'application/json')->withStatus(500);
    }
});


// Actualiza una sección determinada
$app->put('/update_section/{id_section}', function (Request $request, Response $response)
{
    $id = $request->getAttribute('id_section');
    $contentType = $request->getHeaderLine('Content-Type');
    
    // Handle different content types
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON request
        $data = json_decode($request->getBody()->getContents(), true);
        error_log("UPDATE_SECTION received JSON data");
    } else {
        // For multipart/form-data or application/x-www-form-urlencoded
        $data = $request->getParsedBody();
        error_log("UPDATE_SECTION received form data");
    }
    
    // Log received data safely (excluding image data)
    error_log("UPDATE_SECTION received data for section ID: " . $id);
    
    if (!isset($data["section_name"])) {
        throw new Exception("El campo 'section_name' es requerido");
    }
    
    $name = $data["section_name"];
    $imageBase64 = isset($data["section_img"]) ? $data["section_img"] : null;
    
    // Process base64 image if provided
    if ($imageBase64) {
        // Extract the base64 data part if it includes the data URL prefix
        if (strpos($imageBase64, 'data:image') !== false) {
            $parts = explode(',', $imageBase64);
            $imageBase64 = $parts[1];
        }
    }

    try {
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = :id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownername = $stmt->fetchColumn();

        // Get section ID for response
        $sql = "SELECT id_restaurant FROM Sections WHERE id_section = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $restaurantId = $stmt->fetchColumn();

        // Use the correct path for images
        $ruta = "./owners/" . $ownername . "/img/sections/";

        $sql = "SELECT name FROM Sections WHERE id_section = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $oldSectionName = $stmt->fetchColumn();

        // Only try to delete if the file exists
        if (file_exists($ruta . $oldSectionName . ".png")) {
            unlink($ruta . $oldSectionName . ".png");
        }

        // Save the new image
        if ($imageBase64) {
            $decoded_data = base64_decode($imageBase64);
            file_put_contents($ruta . $name . ".png", $decoded_data);
        }

        // Update the section in the database
        $sql = "UPDATE Sections SET name = :name WHERE id_section = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();

        // Create response object with updated section data
        $section = [
            "id_section" => intval($id),
            "name" => $name,
            "id_restaurant" => intval($restaurantId),
            "image" => null,
            "articles" => []
        ];
        
        // Add the image to the response if it exists
        if (file_exists($ruta . $name . ".png")) {
            $imageData = file_get_contents($ruta . $name . ".png");
            $section['image'] = "data:image/png;base64," . base64_encode($imageData);
        }

        $db = null;

        // Return the updated section data
        $response->getBody()->write(json_encode($section));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        error_log("Database error in UPDATE_SECTION: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        error_log("General error in UPDATE_SECTION: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});


// Actualiza un articulo determinado
$app->put('/update_article/{id_article}', function (Request $request, Response $response, array $args)
{
    $id = $request->getAttribute('id_article');
    $contentType = $request->getHeaderLine('Content-Type');
    
    // Check if the request is JSON or multipart form data
    if (strpos($contentType, 'application/json') !== false) {
        // Handle JSON request
        $data = $request->getParsedBody();
        if (!$data) {
            $data = json_decode($request->getBody()->getContents(), true);
        }
        
        if (!isset($data["article_name"]) || !isset($data["article_price"])) {
            throw new Exception("Los campos 'article_name' y 'article_price' son requeridos");
        }
        
        $name = $data["article_name"];
        $price = $data["article_price"];
        $imageBase64 = isset($data["article_img"]) ? $data["article_img"] : null;
        
        // Extract base64 data if it includes the data URL prefix
        if ($imageBase64 && strpos($imageBase64, 'data:image') !== false) {
            $parts = explode(',', $imageBase64);
            $img = $parts[1];
        } else {
            $img = $imageBase64;
        }
    } else {
        // Handle multipart form data
        $data = $request->getParsedBody();
        
        if (!isset($data["article_name"]) || !isset($data["article_price"])) {
            throw new Exception("Los campos 'article_name' y 'article_price' son requeridos");
        }
        
        $name = $data["article_name"];
        $price = $data["article_price"];
        $img = isset($data["article_img"]) && !empty($data["article_img"]) ? 
               explode(",", $data["article_img"])[1] : null;
    }

    try {
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = (SELECT id_section FROM Articles WHERE id_article = :id))";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownername = $stmt->fetchColumn();

        $ruta = "./owners/" . $ownername . "/img/articles/";
        
        // Ensure directory exists
        if (!is_dir($ruta)) {
            if (!mkdir($ruta, 0777, true)) {
                throw new Exception("No se pudo crear el directorio de artículos: " . $ruta);
            }
        }

        $sql = "SELECT name, id_section FROM Articles WHERE id_article = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $articleData = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldArticleName = $articleData['name'];
        $sectionId = $articleData['id_section'];

        // Only try to delete if the file exists
        if (file_exists($ruta . $oldArticleName . ".png")) {
            unlink($ruta . $oldArticleName . ".png");
        }

        // Save the new image if provided
        if ($img) {
            $decoded_data = base64_decode($img);
            file_put_contents($ruta . $name . ".png", $decoded_data);
        }

        // Update the article in the database
        $sql = "UPDATE Articles SET name = :name, price = :price WHERE id_article = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();

        // Create response object with updated article data
        $article = [
            "id_article" => intval($id),
            "name" => $name,
            "price" => floatval($price),
            "id_section" => intval($sectionId),
            "image" => null
        ];
        
        // Add the image to the response if it exists
        if (file_exists($ruta . $name . ".png")) {
            $imageData = file_get_contents($ruta . $name . ".png");
            $article['image'] = "data:image/png;base64," . base64_encode($imageData);
        }

        $db = null;

        // Return the updated article data
        $response->getBody()->write(json_encode($article));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        error_log("Database error in UPDATE_ARTICLE: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        error_log("General error in UPDATE_ARTICLE: " . $e->getMessage());
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});


// Actualiza un trabajador en concreto
$app->put('/update_worker/{id_user}',function (Request $request, Response $response, array $args)
{
    $userid = $request->getAttribute('id_user');
    
    // Log the raw request body for debugging
    $requestBody = $request->getBody()->getContents();
    error_log("UPDATE_WORKER - Raw request body: " . $requestBody);
    
    // Try to parse the request body based on content type
    $contentType = $request->getHeaderLine('Content-Type');
    
    if (strpos($contentType, 'application/json') !== false) {
        // For JSON data
        $data = json_decode($requestBody, true);
        error_log("UPDATE_WORKER - JSON data: " . json_encode($data));
    } else {
        // For form data or other formats
        $data = $request->getParsedBody();
        
        // If parsed body is empty, try to parse manually
        if (empty($data) && strpos($requestBody, '=') !== false) {
            $data = [];
            // Split by commas and process each key-value pair
            $pairs = explode(',', $requestBody);
            foreach ($pairs as $pair) {
                $parts = explode('=', $pair, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    
                    // Handle the special case for roles which might be in C# format
                    if ($key === 'worker_roles' && strpos($value, 'System.Collections.Generic.List') !== false) {
                        // Extract role IDs from the string
                        preg_match_all('/\d+/', $value, $matches);
                        $data[$key] = implode(',', $matches[0]);
                    } else {
                        $data[$key] = $value;
                    }
                }
            }
        }
    }
    
    // Check if we have the required data
    if (empty($data) || !isset($data["worker_name"]) || !isset($data["worker_username"])) {
        error_log("UPDATE_WORKER - Missing required fields in request");
        $error = array("message" => "Datos incompletos en la solicitud");
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('content-type', 'application/json')->withStatus(400);
    }
    
    $name = $data["worker_name"];
    $username = $data["worker_username"];
    
    // Check if password is provided and not empty
    $passwordProvided = isset($data["worker_password"]) && $data["worker_password"] !== "";
    if ($passwordProvided) {
        $password = password_hash($data["worker_password"], PASSWORD_DEFAULT);
        error_log("UPDATE_WORKER - Password will be updated");
    } else {
        error_log("UPDATE_WORKER - Password will not be updated (empty or not provided)");
    }
    
    // Handle roles in different formats
    if (isset($data["worker_roles"])) {
        $roles = $data["worker_roles"];
        
        // Check if roles is already an array
        if (is_array($roles)) {
            error_log("UPDATE_WORKER - Roles is an array with " . count($roles) . " elements");
        } 
        // Otherwise, assume it's a comma-separated string
        else {
            error_log("UPDATE_WORKER - Roles is a string: " . $roles);
            $roles = explode(',', $roles);
        }
    } else {
        $roles = [];
    }
    
    error_log("UPDATE_WORKER - Datos procesados: name={$name}, username={$username}, roles=" . json_encode($roles));

    try {
        $db = new DB();
        $conn = $db->connect();

        // Rest of the function remains the same...
        $sql = "SELECT id_restaurant FROM Users WHERE id_user = $userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $ownerid = $stmt->fetchColumn();

        // Verificar si el nombre de usuario ya existe
        $sql = "SELECT COUNT(*) FROM Users WHERE username = :username AND id_user != :id_user";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id_user', $userid);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            throw new Exception("El nombre de usuario '$username' ya existe.");
        }

        // Modificar el usuario - solo actualizar la contraseña si se proporcionó una nueva
        if ($passwordProvided) {
            $sql = "UPDATE Users SET name = :name, username = :username, password = :password, id_restaurant = :idowner WHERE id_user = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password', $password);
        } else {
            $sql = "UPDATE Users SET name = :name, username = :username, id_restaurant = :idowner WHERE id_user = :userid";
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':idowner', $ownerid);
        $stmt->bindParam(':userid', $userid);
        $stmt->execute();

        // Insertar roles del usuario - no need to explode again, we already have an array
        foreach ($roles as $role) {
            $sql = "SELECT COUNT(*) FROM UsersRoles WHERE id_user = $userid AND id_role = $role";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            // Compruebar los roles nuevos asignados
            if ($count === 0) {
                $sql = "INSERT INTO UsersRoles (id_user, id_role) VALUES (:id_user, :id_role)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_user', $userid);
                $stmt->bindParam(':id_role', $role);
                $stmt->execute();
            }
        }

        // Comprobar los roles desasignados
        $sql = "SELECT id_role FROM UsersRoles WHERE id_user = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = $row['id_role'];
            if (!in_array($role, $roles)) {
                $sql = "DELETE FROM UsersRoles WHERE id_user = :userid AND id_role = :role";
                $stmtDelete = $conn->prepare($sql);
                $stmtDelete->bindParam(':userid', $userid);
                $stmtDelete->bindParam(':role', $role);
                $stmtDelete->execute();
            }
        }


        $db = null;

        $response->getBody()->write(json_encode(array("success" => true)));
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response->withHeader('content-type', 'application/json')->withStatus(500);
    }
});

// Borra una sección determinada
$app->delete('/delete_section/{id_section}', function (Request $request, Response $response, array $args) {
    $id = $args["id_section"];



    try {
        $db = new Db();
        $conn = $db->connect();


        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = :id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownerName = $stmt->fetchColumn();

        $sql = "SELECT name FROM Sections WHERE id_section = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $sectionName = $stmt->fetchColumn();


        $imagenRuta = "./owners/" . $ownerName . "/img/sections/" . $sectionName . ".png";
        unlink($imagenRuta);

        $sql = "DELETE FROM Articles WHERE id_section = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();


        $sql = "DELETE FROM Sections WHERE id_section = $id";
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

// Borra un articulo determinado
$app->delete('/delete_article/{id_article}', function (Request $request, Response $response, array $args) {
    $id = $args["id_article"];




    try {
        $db = new Db();
        $conn = $db->connect();


        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = (SELECT id_section FROM Articles WHERE id_article = :id))";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownerName = $stmt->fetchColumn();

        $sql = "SELECT name FROM Articles WHERE id_article = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $articleName = $stmt->fetchColumn();


        $imagenRuta = "./owners/" . $ownerName . "/img/articles/" . $articleName . ".png";

        unlink($imagenRuta);

        $sql = "DELETE FROM Articles WHERE id_article = $id";
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

// Borra un trabajador en concreto
$app->delete('/delete_worker/{id_worker}', function (Request $request, Response $response, array $args) {
    $id = $args["id_worker"];

    try {
        $db = new Db();
        $conn = $db->connect();





        $sql = "DELETE FROM UsersRoles WHERE id_user = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM Users WHERE id_user = $id";
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
