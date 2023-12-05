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
        $response = new Response();
        if(isset($parametros['idProducto']) && !empty($parametros['idProducto'])){
            $idProducto = $parametros["idProducto"];

            if(Producto::ObtenerProductoPorID($idProducto) !== false){
                return $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "producto no encontrado"]));
            }    
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}