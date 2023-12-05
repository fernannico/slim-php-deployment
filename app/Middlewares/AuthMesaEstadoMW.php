<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMesaEstadoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $response = new Response();
        if(isset($parametros['estado']) && !empty($parametros['estado'])){

            $estado = $parametros['estado'];

            $estadosPermitidos = ["abierta","pidiendo","con cliente esperando pedido", "con cliente comiendo", "con cliente pagando"];
    
            //validamos estado
            if (in_array($estado, $estadosPermitidos)) {
                $response = $handler->handle($request);
            }else{
                $payload = json_encode(array('mensaje' => 'estado no identificado, Estados permitidos:  - abierta, - pidiendo, - con cliente esperando pedido, - con cliente comiendo, - con cliente pagando'));
                $response->getBody()->write($payload);
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }    
}