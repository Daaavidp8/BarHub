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
        
        // Add logo to each owner
        foreach ($owners as $owner) {
            $logoPath = "./owners/" . $owner->name . "/img/logo.png";
            if (file_exists($logoPath)) {
                // Read the image file and convert to base64
                $imageData = file_get_contents($logoPath);
                $base64Image = base64_encode($imageData);
                $owner->logo = "data:image/png;base64," . $base64Image;
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
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();



        $name = $data["owner_name"];
        $cif = $data["owner_CIF"];
        $email = $data["owner_contact_email"];
        $phone = $data["owner_contact_phone"];

        if (!is_dir("./owners")) {
            mkdir("./owners");
        }
        
        $ruta = "./owners/" . $name;


        if (!is_dir($ruta)) {
            mkdir($ruta);
            mkdir($ruta . "/img/sections");
            mkdir($ruta . "/img/articles");
        } else {
            throw new Exception("El directorio ya existe.");
        }

        $uploadedFile = $uploadedFiles['owner_logo'] ?? null;

        if ($uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            // Subimos el logo a la carpeta creada del correspondiente del restaurante
            if ($uploadedFile->getClientMediaType() !== 'image/png') {
                throw new Exception("El archivo subido no tiene extensión PNG.");
            }

            $directorio = $ruta . "/img/";

            $nombreArchivo = 'logo.png';
            $rutaArchivo = $directorio . $nombreArchivo;

            $uploadedFile->moveTo($rutaArchivo);
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
        $db = null;

        // Retornamos una respuesta exitosa
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (Exception $e) {
        // En caso de errores, retornamos un mensaje de error
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// Función para actualizar un restaurante
$app->put('/update_owner/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = $request->getParsedBody();


    $name = $data["owner_name"];
    $cif = $data["owner_CIF"];
    $email = $data["owner_contact_email"];
    $phone = $data["owner_contact_phone"];
    $img = explode(",",$data["owner_logo"])[1];
    $rutaOwners = "../../clientereact/public/images/owners/";


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


        if ($img != null){
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





        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        // Manejar errores de la base de datos
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        // Manejar otros tipos de errores
        $error = array(
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
    $rutaOwners = "../../clientereact/public/images/owners/";

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
