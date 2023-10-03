<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->get('/saludo/{nombre}', function (Request $request, Response $response,$args) {
        $nombre=$args['nombre'];
        $response->getBody()->write('Hola, mi nombre es '.$nombre);
        return $response;
    });

    $app->get('/cliente', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("SELECT * FROM cliente ORDER BY nombre");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/producto', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("SELECT * FROM producto ORDER BY nombre");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/cliente', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $input=$request->getParsedBody();
        $sql="INSERT INTO cliente(ruc,nombre,telefono) VALUES(:ruc,:nombre,:telefono)";
        $sth = $db->prepare($sql);
        $sth->bindParam("ruc",$input['ruc']);
        $sth->bindParam("nombre",$input['nombre']);
        $sth->bindParam("telefono",$input['telefono']);
        $sth->execute();
        $data = $db->lastInsertId();
        $id_json = json_encode($data);
        $response->getBody()->write($id_json);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/producto/{id}', function (Request $request, Response $response,$args) {
        $db = $this->get(PDO::class);
        $input=$request->getParsedBody();
        $productId = $args['id'];
        $cantidad = $input['cantidad'];
        $agregar = $input['agregar']; 

        if($agregar) {
            $sql = "UPDATE producto SET existencia = existencia + :cantidad WHERE producto_id = :id";
        } else {
            $sql = "UPDATE producto SET existencia = existencia - :cantidad WHERE producto_id = :id";
        }

        $sth = $db->prepare($sql);
        $sth->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $sth->bindParam(":id", $productId, PDO::PARAM_INT);
        $sth->execute();
        $id_json = json_encode($productId);
        $response->getBody()->write($id_json);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
