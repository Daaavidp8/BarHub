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
            $imagePath = "./owners/{$restaurantName}/img/sections/{$sectionName}.png";
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                $section['image'] = "data:image/png;base64," . base64_encode($imageData);
            } else {
                $section['image'] = "";
            }
            
            // Get articles for this section
            $sqlArticles = "SELECT * FROM Articles WHERE id_section = :sectionId";
            $stmtArticles = $conn->prepare($sqlArticles);
            $stmtArticles->bindParam(':sectionId', $sectionId, PDO::PARAM_INT);
            $stmtArticles->execute();
            $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
            
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

    $sql = "SELECT * FROM Users WHERE id_restaurant = $ownerid";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($customers));
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
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    $name = $data["section_name"];

    try {
        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = :ownerid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ownerid', $ownerid);
        $result = $stmt->execute();

        if ($result) {
            $ownername = $stmt->fetchColumn();
        }

        $ruta = "./owners/" . $ownername;

        $uploadedFile = $uploadedFiles['section_img'] ?? null;

        if ($uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            if ($uploadedFile->getClientMediaType() !== 'image/png') {
                throw new Exception("El archivo subido no tiene extensión PNG.");
            }

            $directorio = $ruta . "/img/sections/";

            $nombreArchivo = $name . '.png';
            $rutaArchivo = $directorio . $nombreArchivo;

            $uploadedFile->moveTo($rutaArchivo);
        }


        $sql = "INSERT INTO Sections (name, id_restaurant) VALUES (:name, :idowner)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':idowner', $ownerid);

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


// Crea un articulo en una sección determinada
$app->post('/create_article/{id_section}', function (Request $request, Response $response) {
    $sectionid = $request->getAttribute('id_section');
    $data = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();
    $name = $data["article_name"];
    $price = $data["article_price"];


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


        $ruta = "../../clientereact/public/images/owners/" . $ownername;

        $uploadedFile = $uploadedFiles['article_img'] ?? null;

        if ($uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            if ($uploadedFile->getClientMediaType() !== 'image/png') {
                throw new Exception("El archivo subido no tiene extensión PNG.");
            }

            $directorio = $ruta . "/img/articles/";

            $nombreArchivo = $name . '.png';
            $rutaArchivo = $directorio . $nombreArchivo;

            $uploadedFile->moveTo($rutaArchivo);
        }





        $sql = "INSERT INTO Articles (name, price, id_section) VALUES (:name, :price, :idsection)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':idsection', $sectionid);

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


// Crea trabajadores en un restaurante en concreto
$app->post('/create_worker/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');
    $data = $request->getParsedBody();
    $name = $data["worker_name"];
    $username = $data["worker_username"];
    $password = password_hash($data["worker_password"], PASSWORD_DEFAULT);
    $roles = $data["worker_roles"];

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

        // Insertar roles del usuario
        $roles = explode(',', $roles);
        foreach ($roles as $role) {
            $sql = "INSERT INTO UsersRoles (id_user, id_role) VALUES (:id_user, :id_role)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $userid);
            $stmt->bindParam(':id_role', $role);
            $stmt->execute();
        }

        $db = null;

        $response->getBody()->write(json_encode(array("success" => true)));
        return $response->withHeader('content-type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $error = array("error" => $e->getMessage());
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('content-type', 'application/json')->withStatus(500);
    }
});


// Actualiza una sección determinada
$app->put('/update_section/{id_section}', function (Request $request, Response $response)
{
    $id = $request->getAttribute('id_section');
    $data = $request->getParsedBody();
    $name = $data["section_name"];
    $img = explode(",",$data["section_img"])[1];




    try {
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = :id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownername = $stmt->fetchColumn();


        $ruta = "../../clientereact/public/images/owners/" . $ownername . "/img/sections/";

        $sql = "SELECT name FROM Sections WHERE id_section = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $oldSectionName = $stmt->fetchColumn();

        unlink($ruta . $oldSectionName . ".png");


        $decoded_data = base64_decode($img);
        file_put_contents($ruta  . $name . ".png", $decoded_data);


        $sql = "UPDATE Sections SET name = :name WHERE id_section = $id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);

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


// Actualiza un articulo determinado
$app->put('/update_article/{id_article}',function (Request $request, Response $response, array $args)
{
    $id = $request->getAttribute('id_article');
    $data = $request->getParsedBody();
    $name = $data["article_name"];
    $price = $data["article_price"];
    $img = explode(",",$data["article_img"])[1];


    try {
        $db = new Db();
        $conn = $db->connect();

        $sql = "SELECT name FROM Restaurants WHERE id_restaurant = (SELECT id_restaurant FROM Sections WHERE id_section = (SELECT id_section FROM Articles WHERE id_article = :id))";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ownername = $stmt->fetchColumn();



        $ruta = "../../clientereact/public/images/owners/" . $ownername . "/img/articles/";

        $sql = "SELECT name FROM Articles WHERE id_article = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $oldArticleName = $stmt->fetchColumn();

        unlink($ruta . $oldArticleName . ".png");


        $decoded_data = base64_decode($img);
        file_put_contents($ruta  . $name . ".png", $decoded_data);

        $sql = "UPDATE Articles SET name = :name, price = :price WHERE id_article = $id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);

        $result = $stmt->execute();

        $db = null;
        echo "Update successful! ";
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


// Actualiza un trabajador en concreto
$app->put('/update_worker/{id_user}',function (Request $request, Response $response, array $args)
{
    $userid = $request->getAttribute('id_user');
    $data = $request->getParsedBody();
    $name = $data["worker_name"];
    $username = $data["worker_username"];
    var_dump( $data["worker_password"] );
    $password = $data["worker_password"] === "" ? "" : password_hash($data["worker_password"], PASSWORD_DEFAULT);
    var_dump($password);
    $roles = $data["worker_roles"];

    try {
        $db = new DB();
        $conn = $db->connect();

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


        // Modificar el nuevo usuario
        $sql = "UPDATE Users SET name = :name, username = :username,";
        if ($password !== "") {
            $sql .= " password = :password,";
        }
        $sql .= " id_restaurant = :idowner WHERE id_user = :userid";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':idowner', $ownerid);
        $stmt->bindParam(':userid', $userid);
        if ($password !== "") {
            $stmt->bindParam(':password', $password);
        }
        $stmt->execute();

        // Insertar roles del usuario
        $roles = explode(',', $roles);
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
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
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


        $imagenRuta = "../../clientereact/public/images/owners/" . $ownerName . "/img/sections/" . $sectionName . ".png";
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


        $imagenRuta = "../../clientereact/public/images/owners/" . $ownerName . "/img/articles/" . $articleName . ".png";

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
