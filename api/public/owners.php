<?php

global $app;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DB;

// Get sections from specific owner
$app->get('/get_sections/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM Sections WHERE id_restaurant = $id";

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


// Get article from specific section
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


// Get specific article
$app->get('/get_article/{id_article}', function (Request $request, Response $response) {
    $articleid = $request->getAttribute('id_article');
    $sql = "SELECT * FROM Articles WHERE id_article = $articleid";

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

$app->post('/create_section/{id_owner}', function (Request $request, Response $response) {
    $ownerid = $request->getAttribute('id_owner');
    $data = $request->getParsedBody();
    $name = $data["section_name"];


    $sql = "INSERT INTO Sections (name, id_restaurant) VALUES (:name, :idowner)";

    try {
        $db = new DB();
        $conn = $db->connect();

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

$app->post('/create_article/{id_section}', function (Request $request, Response $response) {
    $sectionid = $request->getAttribute('id_section');
    $data = $request->getParsedBody();
    $name = $data["article_name"];
    $price = $data["article_price"];
    $img = $data["article_img"];


    $sql = "INSERT INTO Articles (name, price, id_section) VALUES (:name, :price, :idsection)";

    try {
        $db = new DB();
        $conn = $db->connect();

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



$app->put('/update_section/{id_section}',function (Request $request, Response $response, array $args)
{
    $id = $request->getAttribute('id_section');
    $data = $request->getParsedBody();
    $name = $data["section_name"];

    $sql = "UPDATE Sections SET name = :name WHERE id_section = $id";

    try {
        $db = new Db();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);

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


$app->put('/update_article/{id_article}',function (Request $request, Response $response, array $args)
{
    $id = $request->getAttribute('id_article');
    $data = $request->getParsedBody();
    $name = $data["article_name"];
    $price = $data["article_price"];

    $sql = "UPDATE Articles SET name = :name, price = :price WHERE id_article = $id";

    try {
        $db = new Db();
        $conn = $db->connect();

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

$app->put('/update_worker/{id_user}',function (Request $request, Response $response, array $args)
{
    $userid = $request->getAttribute('id_user');
    $data = $request->getParsedBody();
    $name = $data["worker_name"];
    $username = $data["worker_username"];
    $password = hash('sha256', $data["worker_password"]);
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
        $stmt->bindParam(':id_user', $userrid);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            throw new Exception("El nombre de usuario '$username' ya existe.");
        }

        // Modificar el nuevo usuario
        $sql = "UPDATE Users SET name = :name, username = :username, password = :password, id_restaurant = :idowner WHERE id_user = $userid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':idowner', $ownerid);
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
            var_dump("Rol->" . $role);
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

$app->delete('/delete_section/{id_section}', function (Request $request, Response $response, array $args) {
    $id = $args["id_section"];

    $sql = "DELETE FROM Sections WHERE id_section = $id";

    try {
        $db = new Db();
        $conn = $db->connect();

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

$app->delete('/delete_article/{id_article}', function (Request $request, Response $response, array $args) {
    $id = $args["id_article"];



    try {
        $db = new Db();
        $conn = $db->connect();

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

$app->delete('/delete_worker/{id_worker}', function (Request $request, Response $response, array $args) {
    $id = $args["id_worker"];

    try {
        $db = new Db();
        $conn = $db->connect();



        $sql = "DELETE FROM UsersRoles WHERE id_user = $id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute();

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
