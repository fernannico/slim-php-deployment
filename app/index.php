<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

require_once './controllers/ClienteController.php';
require_once './controllers/EncuestaController.php';
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
require_once './Middlewares/AuthPedidoCodAN.php';
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
    
    # 8- Alguno de los socios pide el listado de las mesas y sus estados.
    $group->get('[/]', \MesaController::class . ':TraerTodas')
            ->add(\AuthSocioMW::class);
    
    # 10- Alguno de los socios cierra la mesa.
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
    # 1- Una moza toma el pedido 
    $group->post('/CargarPedido', \PedidoController::class . ':CargarPedido')
            ->add(\AuthMesaAbiertaPedidoMW::class)  //validar que la mesa no este ocupada (abierta o pidiendo)
            ->add(\AuthProductoMW::class)           //validar que exista el producto
            ->add(\AuthMesaMW::class)               //validar que exista la mesa
            ->add(new AuthSectorMW("mozos"));       //validar el sector

    # 5- Alguno de los socios pide el listado de pedidos y el tiempo de demora de ese pedido.
    $group->get('[/]', \PedidoController::class . ':TraerTodos')
            ->add(\AuthSocioMW::class);      

    # 2- El mozo saca una foto de la mesa y lo relaciona con el pedido.
    $group->post('/relacionarFoto', \PedidoController::class . ':TomarFotoPedidoMesaController')
            ->add(\AuthMesaMW::class)               //validar que exista la mesa
            ->add(new AuthSectorMW("mozos"));

    # 3- Cada empleado responsable de cada producto del pedido , debe: Listar todos los productos pendientes de este tipo de empleado.
    $group->get('/pedidosPendientesSector', \PedidoController::class . ':TraerPedidosPendientesPorSectorController');

    # 6- Cada empleado responsable de cada producto del pedido, debe: Listar todos los productos en preparacion de este tipo de empleado
    $group->get('/pedidosEnPreparacionSector', \PedidoController::class . ':TraerPedidosEnPreparacionPorSectorController');

    # 3b- Debe cambiar el estado a “en preparación” y agregarle el tiempo de preparación.
    $group->put('/tomarPedido', \PedidoController::class . ':TomarPedidoController');
                                                //validar que el pedido a tomar es de su sector
    //     ->add(\pedidosEstadoMW::class)       //estados del pedido previo y finalizado con tiempo

    # 6b- Debe cambiar el estado a “listo para servir” .
    $group->put('/finalizarPedido', \PedidoController::class . ':FinalizarPedidoController');
                                                //validar que el pedido a terminar es de su sector

    # 7- La moza se fija los pedidos que están listos para servir , cambia el estado de la mesa,
    $group->get('/pedidosAEntregar', \PedidoController::class . ':obtenerPedidosListosParaServirController')
            ->add(new AuthSectorMW("mozos"));   //validar el sector
    # 7-b
    $group->put('/entregarPedidos', \PedidoController::class . ':EntregarPedidoController')
            ->add(\AuthPedidoCodAN::class)      //validar que existe el codigoAN
            ->add(new AuthSectorMW("mozos"));   //validar el sector
            
    # 9- La moza cobra la cuenta.
    $group->put('/cobrarCuenta', \UsuarioController::class . ':CobrarCuentaController')
            ->add(\AuthPedidoCodAN::class)      //validar que existe el codigoAN
            ->add(new AuthSectorMW("mozos"));   //validar el sector
})->add(\AuthLoginMW::class);                   //validar que haya token

$app->group('/clientes', function (RouteCollectorProxy $group) {
    # 4- El cliente ingresa el código de la mesa junto con el número de pedido y ve el tiempo de demora de su pedido.
    $group->get('/demoraPedido', \ClienteController::class . ':ObtenerTiempoRestantePedidoController')
            ->add(new AuthSectorMW("cliente"))
            ->add(\AuthMesaMW::class);          //validar que exista la mesa      
    $group->put('/indicarPagar', \ClienteController::class . ':FinalizarComiendoYPagarController')
            ->add(\AuthMesaMW::class);          //validar que exista la mesa      
    
    # 11- El cliente ingresa el código de mesa y el del pedido junto con los datos de la encuesta.
    $group->post('/CargarEncuesta', \EncuestaController::class . ':CargarEncuesta');
                //validar que el pedido no este ya encuestado
});

$app->group('/estadisticas', function (RouteCollectorProxy $group) {
    # 12- Alguno de los socios pide los mejores comentarios (de los 4 puntajes, el promedio)
    $group->get('/mejoresComentarios', \EncuestaController::class . ':ObtenerMejoresComentariosController');

    # 13- Alguno de los socios pide la mesa más usada.
    $group->get('/mesaMasUsada', \MesaController::class . ':MesaMasUsadaController');

});

$app->run();
?>