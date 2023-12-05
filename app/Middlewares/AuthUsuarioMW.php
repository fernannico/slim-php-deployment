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
        $response = new Response();
        if(isset($parametros['idUsuario']) && !empty($parametros['idUsuario'])){
            $idUsuario = $parametros["idUsuario"];
    
            if(Usuario::ObtenerUsuarioPorID($idUsuario) !== false){
                return $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["mensaje" => "usuario no encontrado"]));
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}