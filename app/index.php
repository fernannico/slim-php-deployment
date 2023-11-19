<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/LoginController.php';
require_once './db/AccesoDatos.php';
require_once './Middlewares/AuthSectorPuestoMW.php';
require_once './Middlewares/pedidosEstadoMW.php';
require_once './Middlewares/AuthMesaMW.php';
require_once './Middlewares/AuthSocioMW.php';
require_once './Middlewares/AuthMesaEstadoMW.php';
require_once './Middlewares/LoggerMW.php';
require_once './Middlewares/AuthSectorMW.php';
require_once './JWT/AuthJWT.php';

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
$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', \LoginController::class . ':LoginController');
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->post('[/]', \UsuarioController::class . ':CargarUsuario')->add(\AuthSocioMW::class)->add(\AuthSectorPuestoMW::class)->add(\LoggerMW::class);
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\LoggerMW::class);
    $group->get('/mostrarUno', \UsuarioController::class . ':TraerUno')->add(\LoggerMW::class);  
    $group->put('/cerrarMesa', \UsuarioController::class . ':CerrarMesaController')->add(\AuthSocioMW::class)->add(\AuthMesaMW::class)->add(\LoggerMW::class);
    $group->put('/estadoMesa', \UsuarioController::class . ':CambiarEstadoMesaController')->add(\AuthMesaEstadoMW::class)->add(new AuthSectorMW("mozos"))->add(\LoggerMW::class);
    $group->get('/descargarEnCsv', \UsuarioController::class . ':DescargarUsuariosDesdeCsv')->add(\AuthSocioMW::class);//hacer MW que valide que existe un archivo->add(\LoggerMW::class) 
    $group->post('/cargarCsv', \UsuarioController::class . ':CargarUsuariosDesdeCsv')->add(\AuthSocioMW::class);//hacer MW que valide que existe un archivo->add(\LoggerMW::class) 
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \MesaController::class . ':CargarMesa')->add(\AuthSocioMW::class)->add(\LoggerMW::class);
    $group->get('[/]', \MesaController::class . ':TraerTodas')->add(\LoggerMW::class);
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ProductoController::class . ':CargarProducto')->add(\AuthSocioMW::class)->add(\LoggerMW::class);
    $group->get('[/]', \ProductoController::class . ':TraerTodos')->add(\LoggerMW::class);
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \PedidoController::class . ':CargarPedido')->add(new AuthSectorMW("mozos"))->add(\LoggerMW::class);
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(\LoggerMW::class);
    $group->put('/estado', \PedidoController::class . ':ModificarEstado')->add(\pedidosEstadoMW::class)->add(\LoggerMW::class);
});

$app->run();
?>