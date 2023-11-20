<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';
require_once './models/Mesa.php';

class AuthUsuarioMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idUsuario = $parametros["idUsuario"];

        if(Usuario::ObtenerUsuarioPorID($idUsuario) !== false){
            return $handler->handle($request);
        }else{
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "usuario no encontrado"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}