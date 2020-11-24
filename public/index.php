<?php

use App\Controllers\DetallePedidoController;
use App\Controllers\EncuestaController;
use App\Controllers\MesaController;
use App\Controllers\TrabajadorController;
use App\Controllers\PedidoController;
use App\Controllers\TrabajadoresEnPedidoController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\JsonMiddleware;
use Config\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/TPComanda/public'); //Recordar ajustar al mover a un hosting
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

new Database();

$app->group('/empleados',function (RouteCollectorProxy $group) {
    $group->post('[/]', TrabajadorController::class.":add");
    $group->post('/login[/]', TrabajadorController::class.":login");
    $group->put('/{id}/estado[/]', TrabajadorController::class.":updateEstado")->add(new AuthMiddleware);
    $group->put('/{id}/tipo[/]', TrabajadorController::class.":updateTipo")->add(new AuthMiddleware);
    $group->delete('/{id}[/]', TrabajadorController::class.":delete")->add(new AuthMiddleware);
    $group->get('[/]', TrabajadorController::class.":getAll")->add(new AuthMiddleware);
})->add(new JsonMiddleware);

$app->group('/mesas',function (RouteCollectorProxy $group) {
    $group->post('[/]', MesaController::class.":add");
    $group->put('/{id}[/]', MesaController::class.":updateEstado")->add(new AuthMiddleware);
    $group->get('[/]', MesaController::class.":getAll");
})->add(new JsonMiddleware);

$app->group('/pedidos',function (RouteCollectorProxy $group) {
    $group->post('[/]', PedidoController::class.":add")->add(new AuthMiddleware);
    $group->get('[/]', DetallePedidoController::class.":getAll")->add(new AuthMiddleware);
    $group->post('/tomar/{id}[/]', TrabajadoresEnPedidoController::class.":add")->add(new AuthMiddleware);
    $group->put('/listo/{id}[/]', DetallePedidoController::class.":updateEstado")->add(new AuthMiddleware);
    $group->get('/tiempo[/]', DetallePedidoController::class.":getTiempo");
    $group->post('/servir/{id}[/]', PedidoController::class.":servirPedido")->add(new AuthMiddleware);
    $group->delete('/{id}[/]', PedidoController::class.":delete")->add(new AuthMiddleware);
})->add(new JsonMiddleware);

$app->post("/encuesta[/]", EncuestaController::class.":add")->add(new JsonMiddleware);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->run();
