<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './db/AccesoDatos.php';
require_once './middlewares/AuthUsuariosMW.php';
require_once './middlewares/pedidosMW.php';
require_once './middlewares/AuthMesaMW.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();
// Set base path
$app->setBasePath('/slim-deployment/slim-php-deployment/app');
// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->post('[/]', \UsuarioController::class . ':CargarUsuario')->add(new AuthMWSector());
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');  //fx poniendo app/usuarios/4
    $group->put('/cerrarMesa', \UsuarioController::class . ':CerrarMesaController')->add(\AuthMesaMW::class);
    $group->put('/estadoMesa', \UsuarioController::class . ':CambiarEstadoMesaController')/*->add(\AuthMesaMW::class)*/;
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \MesaController::class . ':CargarMesa');
    $group->get('[/]', \MesaController::class . ':TraerTodas');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ProductoController::class . ':CargarProducto');
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \PedidoController::class . ':CargarPedido');
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->put('/estado', \PedidoController::class . ':ModificarEstado')->add(\pedidosMW::class);
});

$app->run();
?>