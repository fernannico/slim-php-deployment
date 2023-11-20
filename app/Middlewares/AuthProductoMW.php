<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Producto.php';
require_once './models/Mesa.php';

class AuthProductoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idProducto = $parametros["id"];

        if(Producto::ObtenerProductoPorID($idProducto) !== false){
            return $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "producto no encontrado"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}