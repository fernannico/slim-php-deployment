<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMesaAbiertaPedidoMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        $idMesa = $parametros['idMesa'];

        //validamos estado
        if (Mesa::ObtenerEstadoPorID($idMesa) === 'abierta' || Mesa::ObtenerEstadoPorID($idMesa) === 'pidiendo' ){
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'La mesa esta ocupada o cerrada. Para hacer un pedido la mesa tiene que estar abierta o con estado "pidiendo"'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }    
}