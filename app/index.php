<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

require_once './controllers/LoginController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/UsuarioController.php';
require_once './db/AccesoDatos.php';
require_once './JWT/AuthJWT.php';
require_once './Middlewares/AuthCantSocios.php';
require_once './Middlewares/AuthLoginMW.php';
require_once './Middlewares/AuthMesaAbiertaPedidoMW.php';
require_once './Middlewares/AuthMesaEstadoMW.php';
require_once './Middlewares/AuthMesaMW.php';
require_once './Middlewares/AuthPedidosEstadoMW.php';
require_once './Middlewares/AuthProductoMW.php';
require_once './Middlewares/AuthSectorMW.php';
require_once './Middlewares/AuthSectorPuestoMW.php';
require_once './Middlewares/AuthSocioMW.php';
require_once './Middlewares/AuthUsuarioEstadoMW.php';
require_once './Middlewares/AuthUsuarioMW.php';

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
    $group->post('[/]', \UsuarioController::class . ':CargarUsuario')
            ->add(\AuthSectorPuestoMW::class)   //validar puesto con sector
            ->add(\AuthCantSocios::class);      //validar que no sean mas de 3 socios
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');            
    $group->get('/mostrarUno', \UsuarioController::class . ':TraerUno');              
    $group->delete('/estadoUsuario', \UsuarioController::class . ':CambiarEstadoUsuarioController')
            ->add(\AuthUsuarioMW::class)        //validar que exista el usuario
            ->add(\AuthUsuarioEstadoMW::class); //validar estados posibles
    $group->put('/modificarUsuario', \UsuarioController::class . ':ModificarUsuarioController')
            ->add(\AuthUsuarioMW::class)        //validar que exista el usuario
            ->add(\AuthSectorPuestoMW::class)   //validar puesto con sector
            ->add(\AuthCantSocios::class);      //validar que no sean mas de 3 socios
    $group->get('/descargarEnCsv', \UsuarioController::class . ':DescargarUsuariosDesdeCsv');
    $group->post('/cargarCsv', \UsuarioController::class . ':CargarUsuariosDesdeCsv');
})->add(\AuthLoginMW::class)->add(\AuthSocioMW::class);                   


$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \MesaController::class . ':CargarMesa')
            ->add(\AuthSocioMW::class);         //validar que es socio
    $group->get('[/]', \MesaController::class . ':TraerTodas');
    $group->delete('/cerrarMesa', \UsuarioController::class . ':CerrarMesaController')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\AuthMesaMW::class);          //validar que exista la mesa
    $group->put('/estadoMesa', \UsuarioController::class . ':CambiarEstadoMesaController')
            ->add(\AuthMesaMW::class)           //validar que exista la mesa
            ->add(\AuthMesaEstadoMW::class)     //validar estados posibles
            ->add(new AuthSectorMW("mozos"));   //validar el sector
})->add(\AuthLoginMW::class);                   //validar que haya token


$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ProductoController::class . ':CargarProducto')
            ->add(\AuthSocioMW::class);         //validar que es socio
    $group->get('[/]', \ProductoController::class . ':TraerTodos');     
    $group->put('/modificarProducto', \ProductoController::class . ':ModificarProductoController')
            ->add(\AuthProductoMW::class)       //validar que exista el producto
            ->add(\AuthSocioMW::class);         //validar que es socio
})->add(\AuthLoginMW::class);                   //validar que haya token


$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('/CargarPedido', \PedidoController::class . ':CargarPedido')
            ->add(\AuthMesaMW::class)               //validar que exista la mesa
            ->add(\AuthProductoMW::class)           //validar que exista el producto
            ->add(\AuthMesaAbiertaPedidoMW::class)  //validar que la mesa no este ocupada (abierta o pidiendo)
            ->add(new AuthSectorMW("mozos"));       //validar el sector
    $group->get('[/]', \PedidoController::class . ':TraerTodos');      
    $group->post('/relacionarFoto', \PedidoController::class . ':TomarFotoPedidoMesaController')
            ->add(\AuthMesaMW::class)               //validar que exista la mesa
            ->add(new AuthSectorMW("mozos"));
    $group->get('/pedidosPendientesSector', \PedidoController::class . ':TraerPedidosPendientesPorSectorController');
    $group->put('/tomarPedido', \PedidoController::class . ':TomarPedidoController');
                                                //validar que el pedido a tomar es de su sector
    //     ->add(\pedidosEstadoMW::class)       //estados del pedido previo y finalizado con tiempo
    $group->put('/finalizarPedido', \PedidoController::class . ':FinalizarPedidoController');
                                                //validar que el pedido a terminar es de su sector
    $group->get('/pedidosAEntregar', \PedidoController::class . ':obtenerPedidosListosParaServirController')
            ->add(new AuthSectorMW("mozos"));   //validar el sector
    $group->put('/entregarPedidos', \PedidoController::class . ':EntregarPedidoController')
            ->add(new AuthSectorMW("mozos"));   //validar el sector
})->add(\AuthLoginMW::class);                   //validar que haya token


$app->run();
?>