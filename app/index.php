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
require_once './Middlewares/AuthUsuarioMW.php';
require_once './Middlewares/AuthProductoMW.php';
require_once './Middlewares/AuthSocioMW.php';
require_once './Middlewares/AuthCantSocios.php';
require_once './Middlewares/AuthMesaEstadoMW.php';
require_once './Middlewares/AuthMesaAbiertaPedidoMW.php';
require_once './Middlewares/AuthUsuarioEstadoMW.php';
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
    //alta
    $group->post('[/]', \UsuarioController::class . ':CargarUsuario')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\AuthSectorPuestoMW::class)   //validar puesto con sector
            ->add(\AuthCantSocios::class)       //validar que no sean mas de 3 socios
            ->add(\LoggerMW::class);            //validar que haya token
    //show
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\LoggerMW::class);            //validar que haya token
    $group->get('/mostrarUno', \UsuarioController::class . ':TraerUno')->add(\LoggerMW::class);            //validar que haya token  
    //baja
    $group->delete('/estadoUsuario', \UsuarioController::class . ':CambiarEstadoUsuarioController')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\AuthUsuarioMW::class)        //validar que exista el usuario
            ->add(\AuthUsuarioEstadoMW::class)  //validar estados posibles
            ->add(\LoggerMW::class);            //validar que haya token
    //modificacion
    $group->put('/modificarUsuario', \UsuarioController::class . ':ModificarUsuarioController')
            ->add(\AuthUsuarioMW::class)        //validar que exista el usuario
            ->add(\AuthSectorPuestoMW::class)   //validar puesto con sector
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\AuthCantSocios::class)       //validar que no sean mas de 3 socios
            ->add(\LoggerMW::class);            //validar que haya token
    //CSV
    $group->get('/descargarEnCsv', \UsuarioController::class . ':DescargarUsuariosDesdeCsv')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\LoggerMW::class);            //validar que haya token
    $group->post('/cargarCsv', \UsuarioController::class . ':CargarUsuariosDesdeCsv')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\LoggerMW::class);            //validar que haya token
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    //alta
    $group->post('[/]', \MesaController::class . ':CargarMesa')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\LoggerMW::class);            //validar que haya token
    //show
    $group->get('[/]', \MesaController::class . ':TraerTodas')
            ->add(\LoggerMW::class);            //validar que haya token
    //baja
    $group->delete('/cerrarMesa', \UsuarioController::class . ':CerrarMesaController')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\AuthMesaMW::class)           //validar que exista la mesa
            ->add(\LoggerMW::class);            //validar que haya token
    //modificacion
    $group->put('/estadoMesa', \UsuarioController::class . ':CambiarEstadoMesaController')
            ->add(\AuthMesaMW::class)           //validar que exista la mesa
            ->add(\AuthMesaEstadoMW::class)     //validar estados posibles
            ->add(new AuthSectorMW("mozos"))     //validar el sector
            ->add(\LoggerMW::class);            //validar que haya token
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    //alta
    $group->post('[/]', \ProductoController::class . ':CargarProducto')
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\LoggerMW::class);            //validar que haya token
    //show
    $group->get('[/]', \ProductoController::class . ':TraerTodos')->add(\LoggerMW::class);            //validar que haya token
    //modificacion
    $group->put('/modificarProducto', \ProductoController::class . ':ModificarProductoController')
            ->add(\AuthProductoMW::class)       //validar que exista el producto
            ->add(\AuthSocioMW::class)          //validar que es socio
            ->add(\LoggerMW::class);            //validar que haya token
});

//hacer lo de pedidos como se debe
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    //alta
    $group->post('[/]', \PedidoController::class . ':CargarPedido')
            ->add(\AuthMesaMW::class)               //validar que exista la mesa
            ->add(\AuthProductoMW::class)           //validar que exista el producto
            ->add(\AuthMesaAbiertaPedidoMW::class)  //validar que la mesa no este ocupada (abierta o pidiendo)
            ->add(new AuthSectorMW("mozos"))        //validar el sector
            ->add(\LoggerMW::class);                //validar que haya token
    //show
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(\LoggerMW::class);            //validar que haya token
    
    // show por sector (token)
    $group->get('/pedidosPendientesSector', \PedidoController::class . ':TraerPedidosPendientesPorSectorController')
                                                    //validar que el sector tenga pedidos
                                                    //validar que no sea mozo
            ->add(\LoggerMW::class);                //validar que haya token

    //modificacion (estado--> tomar pedido)
    $group->put('/estado', \PedidoController::class . ':TomarPedidoController')
            ->add(\pedidosEstadoMW::class)      //estados del pedido previo y finalizado
            ->add(\LoggerMW::class);            //validar que haya token

    //show pedidos para entregar
    $group->get('/pedidosAEntregar', \PedidoController::class . ':obtenerPedidosListosParaServirController')
            ->add(new AuthSectorMW("mozos"))        //validar el sector
            ->add(\LoggerMW::class);                //validar que haya token

    //modificacion (estado2--> entregar pedido)
    $group->put('/entregarPedidos', \PedidoController::class . ':EntregarPedidoController')
            ->add(new AuthSectorMW("mozos"))        //validar el sector
            ->add(\LoggerMW::class);                //validar que haya token

});

$app->run();
?>