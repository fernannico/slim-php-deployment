<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Pedido.php';
require_once './models/Mesa.php';

class AuthPedidoIdMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $response = new Response();
        if(isset($parametros['idPedido']) && !empty($parametros['idPedido'])){
            $idPedido = $parametros["idPedido"];
    
            if(Pedido::ObtenerPedidoPorID($idPedido) !== false){
                return $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "pedido no encontrado"]));
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}