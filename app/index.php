<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

require_once './controllers/UsuarioController.php';
// require_once(__DIR__ . '/../db/AccesoDatos.php');
require_once './db/AccesoDatos.php';

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
    $group->post('[/]', \UsuarioController::class . ':CargarUsuario');
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    // $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    });

$app->run();
?>