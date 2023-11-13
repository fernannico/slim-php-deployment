<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMesaEstadoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        $estado = $parametros['estado'];

        $estadosPermitidos = ["con cliente esperando pedido", "con cliente comiendo", "con cliente pagando"];

        //validamos estado
        if (in_array($estado, $estadosPermitidos)) {
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'estado no identificado<BR>Estados permitidos: <br> - con cliente esperando pedido,<br> - con cliente comiendo,<br> - con cliente pagando'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }    
}