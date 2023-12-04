<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';
require_once './models/Mesa.php';

class AuthPedidoCodAN
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        if($parametros != null){
            $codigoAN = $parametros["codigoAN"];
        }else{
            $queryParams = $request->getQueryParams();
            $codigoAN = $queryParams["codigoAN"];
        }

        // var_dump($idMesa);

        if(!empty(Pedido::obtenerPedidosPorCodigoAN($codigoAN))){
            return $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "No se encontro el pedido con dicho codigoAN"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}