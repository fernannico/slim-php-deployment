<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthUsuarioEstadoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        $estado = $parametros['estado'];

        $estadosPermitidos = ["suspendido", "despedido", "activo"];

        //validamos estado
        if (in_array($estado, $estadosPermitidos)) {
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'estado no identificado<BR>Estados permitidos: 
                - suspendido,
                - despedido,
                - activo'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }    
}