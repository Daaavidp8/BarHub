<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

$app->get('/get_owners', function (Request $request, Response $response) {
    $sql = "SELECT * FROM Restaurants";

    try {
        $db = new Db();
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



$app->post('/create_owner', function (Request $request, Response $response) {
    try {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        // Verificamos si se ha enviado un archivo
        if (!isset($uploadedFiles['owner_logo']) || !$uploadedFiles['owner_logo']->getError() === UPLOAD_ERR_OK) {
            throw new Exception("No se ha recibido el archivo o ha ocurrido un error en la subida.");
        }

        $name = $data["owner_name"];
        $cif = $data["owner_CIF"];
        $email = $data["owner_contact_email"];
        $phone = $data["owner_contact_phone"];
        $uploadedFile = $uploadedFiles['owner_logo'];


        // Verificamos si el archivo es una imagen PNG
        if ($uploadedFile->getClientMediaType() !== 'image/png') {
            throw new Exception("El archivo subido no tiene extensión PNG.");
        }

        $ruta = "../../owners/" . $name;

        // Verificamos si el directorio ya existe
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
            mkdir($ruta . "/img/sections", 0777, true);
            mkdir($ruta . "/img/articles", 0777, true);
        } else {
            throw new Exception("El directorio ya existe.");
        }

        // Directorio donde se guardará el archivo
        $directorio = $ruta . "/img/";

        // Movemos el archivo al directorio deseado
        $nombreArchivo = 'logo.png'; // Nombre que se le dará al archivo
        $rutaArchivo = $directorio . $nombreArchivo;

        // Intentamos mover el archivo
        if (!$uploadedFile->moveTo($rutaArchivo)) {
            throw new Exception("Error al mover el archivo.");
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
        $result = $stmt->execute();
        $db = null;

        // Retornamos una respuesta exitosa
        return $response
            ->withHeader('content-type', 'application/json')
            ->withJson(["message" => "Propietario creado exitosamente."], 200);
    } catch (Exception $e) {
        // En caso de errores, retornamos un mensaje de error
        return $response
            ->withHeader('content-type', 'application/json')
            ->withJson(["error" => $e->getMessage()], 500);
    }
});



$app->put('/update_owner/{id}',function (Request $request, Response $response, array $args)
{
    $id = $request->getAttribute('id');
    $data = $request->getParsedBody();
    $name = $data["owner_name"];
    $cif = $data["owner_CIF"];
    $email = $data["owner_contact_email"];
    $phone = $data["owner_contact_phone"];
    $rutaOwners = "../../owners/";

    try {
        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = $id";

        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $lastFolderName = $stmt->fetchColumn();
        $lastFolderName = $rutaOwners . $lastFolderName;
        $newFolderName = $rutaOwners . $name;


        if (file_exists($lastFolderName)){
            if (!file_exists($newFolderName)){
                    rename($lastFolderName,$newFolderName);
            }else{
                throw new Exception("New Directory name already exists");
            }
        }else{
            throw new Exception("Last Directory name not found");
        }

        $sql = "UPDATE Restaurants SET name = :name, cif = :cif,contactEmail = :email,contactPhone = :phone WHERE id_restaurant = $id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':cif', $cif);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

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

$app->delete('/delete_owner/{id}', function (Request $request, Response $response, array $args) {

    $id = $args["id"];
    $rutaOwners = "../../owners/";



    try {
        // Delete from owners directory

        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = $id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $folderName = $stmt->fetchColumn();
        $folderName = $rutaOwners . $folderName;
        var_dump($folderName);

        borrar_directorio($folderName);
         // Delete from database

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
