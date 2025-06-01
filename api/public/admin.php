<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;


// Función que devuelve todos los restaurantes
$app->get('/get_owners', function (Request $request, Response $response) {
    $sql = "SELECT * FROM Restaurants";
    try {
        $db = new Db();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $owners = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        // Add logo to each owner using URL instead of base64
        foreach ($owners as $owner) {
            $logoRelativePath = "/owners/" . $owner->name . "/img/logo.png";
            $logoFullPath = "." . $logoRelativePath; // For file_exists check
            
            if (file_exists($logoFullPath)) {
                // Use URL instead of base64
                $owner->logo = BASE_URL . $logoRelativePath;
            } else {
                $owner->logo = null;
            }
        }

        // Log the response data
        $jsonResponse = json_encode($owners);
        
        // Send the response
        $response->getBody()->write($jsonResponse);
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


// Devuelve un restaurante especifico
$app->get('/get_owner/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM Restaurants WHERE id_restaurant = $id";

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


// Función para crear un restaurante
$app->post('/create_owner', function (Request $request, Response $response) {
    try {
        // Get data from request based on content type
        $contentType = $request->getHeaderLine('Content-Type');
        
        // Handle different content types
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
            error_log("CREATE_OWNER received JSON data");
        } else {
            // For multipart/form-data or application/x-www-form-urlencoded
            $data = $request->getParsedBody();
            error_log("CREATE_OWNER received form data");
        }
        
        // Log received data safely
        error_log("CREATE_OWNER received data: " . ($data ? json_encode(array_keys($data)) : "No data received"));
        
        $name = filter_var($data["owner_name"], FILTER_SANITIZE_STRING);
        $cif = filter_var($data["owner_CIF"], FILTER_SANITIZE_STRING);
        $email = filter_var($data["owner_contact_email"], FILTER_SANITIZE_STRING);
        $phone = filter_var($data["owner_contact_phone"], FILTER_SANITIZE_STRING);
        $logoBase64 = isset($data["owner_logo"]) ? $data["owner_logo"] : null;
        
        // Create owners directory if it doesn't exist
        if (!is_dir("./owners")) {
            mkdir("./owners");
        }
        
        $ruta = "./owners/" . $name;

        if (!is_dir($ruta)) {
            mkdir($ruta);
            mkdir($ruta . "/img");
            mkdir($ruta . "/img/sections");
            mkdir($ruta . "/img/articles");
        } else {
            throw new Exception("El directorio ya existe.");
        }

        // Process base64 image if provided
        $logoPath = null;
        $processedLogo = null;
        if ($logoBase64) {
            // Extract the base64 data part if it includes the data URL prefix
            if (strpos($logoBase64, 'data:image') !== false) {
                $parts = explode(',', $logoBase64);
                $logoBase64 = $parts[1];
                $processedLogo = $logoBase64; // Store processed logo for response
            } else {
                $processedLogo = $logoBase64;
            }
            
            $directorio = $ruta . "/img/";
            $nombreArchivo = 'logo.png';
            $rutaArchivo = $directorio . $nombreArchivo;
            $logoPath = $rutaArchivo;
            
            // Decode and save the image
            $decodedImage = base64_decode($logoBase64);
            if ($decodedImage === false) {
                throw new Exception("Error al decodificar la imagen en base64.");
            }
            
            if (file_put_contents($rutaArchivo, $decodedImage) === false) {
                throw new Exception("Error al guardar la imagen.");
            }
        }

        // Insertamos los datos en la base de datos
        $sql = "INSERT INTO Restaurants (name, cif, contactEmail, contactPhone) VALUES (:name, :cif, :email, :phone)";
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':cif', $cif);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $lastInsertId = $conn->lastInsertId();
        
        // Log the last insert ID for debugging
        error_log("CREATE_OWNER last insert ID: " . $lastInsertId);
        
        $db = null;
        
        // Format the response to match the C# Restaurant model
        $restaurant = [
            "Id" => intval($lastInsertId),
            "Name" => $name,
            "Cif" => $cif,
            "Email" => $email,
            "Phone" => $phone,
            "Logo" => null,
            "Sections" => [],
            "Users" => []
        ];
        
        // Log the restaurant object for debugging
        error_log("CREATE_OWNER restaurant object: " . json_encode($restaurant));
        
        // Add logo to the restaurant data if it exists
        if ($logoPath && file_exists($logoPath)) {
            // Use a smaller logo for the response to avoid JSON size issues
            $restaurant['Logo'] = "data:image/png;base64," . $processedLogo;
        }
        
        // Ensure proper JSON encoding with error handling
        $jsonResponse = json_encode($restaurant, JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($jsonResponse === false) {
            throw new Exception("Error encoding JSON response: " . json_last_error_msg());
        }
        
        // Retornamos una respuesta exitosa con el restaurante completo
        $response->getBody()->write($jsonResponse);
        
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (Exception $e) {
        // En caso de errores, retornamos un mensaje de error
        $errorResponse = json_encode([
            "error" => $e->getMessage()
        ]);
        
        $response->getBody()->write($errorResponse);
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Función para actualizar un restaurante
$app->put('/update_owner/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    
    // Get data from request based on content type
    $contentType = $request->getHeaderLine('Content-Type');
    
    // Handle different content types
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode($request->getBody()->getContents(), true);
        error_log("UPDATE_OWNER received JSON data");
    } else {
        // For multipart/form-data or application/x-www-form-urlencoded
        $data = $request->getParsedBody();
        error_log("UPDATE_OWNER received form data");
    }
    
    // Log received data safely (excluding logo)
    $logData = $data ? array_filter($data, function($key) {
        return $key !== 'owner_logo';
    }, ARRAY_FILTER_USE_KEY) : [];
    error_log("UPDATE_OWNER received data values: " . json_encode($logData));

    $name = $data["owner_name"];
    $cif = $data["owner_CIF"];
    $email = $data["owner_contact_email"];
    $phone = $data["owner_contact_phone"];
    
    // Handle logo data which might be in different formats
    $logoBase64 = isset($data["owner_logo"]) ? $data["owner_logo"] : null;
    $img = null;
    if ($logoBase64) {
        // Extract the base64 data part if it includes the data URL prefix
        if (strpos($logoBase64, 'data:image') !== false) {
            $img = explode(',', $logoBase64)[1];
        } else {
            $img = $logoBase64;
        }
    }
    
    $rutaOwners = "./owners/";

    try {
        $db = new DB();
        $conn = $db->connect();

        // Verificar si el nuevo nombre de propietario ya existe en la base de datos
        $sql_check = "SELECT COUNT(*) FROM Restaurants WHERE name = :name AND id_restaurant != :id";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':name', $name);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            throw new Exception("El nuevo nombre de propietario ya está en uso.");
        }

        // Verificar si el nombre de la carpeta debe actualizarse
        $sql_get_old_name = "SELECT name FROM Restaurants WHERE id_restaurant = :id";
        $stmt_get_old_name = $conn->prepare($sql_get_old_name);
        $stmt_get_old_name->bindParam(':id', $id);
        $stmt_get_old_name->execute();
        $old_name = $stmt_get_old_name->fetchColumn();

        // Actualizar la información del propietario en la base de datos
        $sql_update = "UPDATE Restaurants SET name = :name, cif = :cif, contactEmail = :email, contactPhone = :phone WHERE id_restaurant = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':name', $name);
        $stmt_update->bindParam(':cif', $cif);
        $stmt_update->bindParam(':email', $email);
        $stmt_update->bindParam(':phone', $phone);
        $stmt_update->bindParam(':id', $id);
        $stmt_update->execute();

        $imagePath = $rutaOwners . $old_name . "/img/logo.png";

        if ($img != null) {
            $decoded_data = base64_decode($img);
            file_put_contents($imagePath, $decoded_data);
        }

        if ($name !== $old_name) {
            $old_folder_path = $rutaOwners . $old_name;
            $new_folder_path = $rutaOwners . $name;

            if (file_exists($old_folder_path)) {
                rename($old_folder_path, $new_folder_path);
            } else {
                throw new Exception("El directorio original no existe.");
            }
        }

        // Return success response with updated data
        $response->getBody()->write(json_encode([
            "status" => true,
            "message" => "Restaurante actualizado correctamente",
            "id" => $id
        ]));
        
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        // Manejar errores de la base de datos
        $error = array(
            "status" => false,
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        // Manejar otros tipos de errores
        $error = array(
            "status" => false,
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


// Funcion para borrar el directorio donde se guardan las imagenes de un restaurante
function borrar_directorio($directorio)
{
    if (is_dir($directorio)) {
        $archivos = scandir($directorio);

        foreach ($archivos as $archivo) {
            // Ignorar los directorios "." y ".."
            if ($archivo != '.' && $archivo != '..') {
                $ruta = $directorio . '/' . $archivo;

                if (is_dir($ruta)) {
                    borrar_directorio($ruta);
                } else {
                    unlink($ruta);
                }
            }
        }

        rmdir($directorio);
    }
}

// Borra un restaurante en concreto
$app->delete('/delete_owner/{id}', function (Request $request, Response $response, array $args) {
    $id = $args["id"];
    $rutaOwners = "./owners/";

    try {
        // Borrar Carpeta
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $folderName = $stmt->fetchColumn();
        $folderName = $rutaOwners . $folderName;

        borrar_directorio($folderName);

        // Borrar de la base de datos todo lo relacionado con ese restaurante

        $sql = "DELETE FROM Articles WHERE id_section IN (SELECT id_section FROM Sections WHERE id_restaurant = $id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM UsersRoles WHERE id_user IN (SELECT id_user FROM Users WHERE id_restaurant = $id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM DinnerTables WHERE id_restaurant = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM Sections WHERE id_restaurant = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM Users WHERE id_restaurant = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $sql = "DELETE FROM Restaurants WHERE id_restaurant = $id";
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
