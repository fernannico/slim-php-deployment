<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';
require_once './models/Mesa.php';

class AuthMesaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        if($parametros != null){
            $idMesa = $parametros["idMesa"];
        }else{
            $queryParams = $request->getQueryParams();
            $idMesa = $queryParams["idMesa"];
        }

        // var_dump($idMesa);
        $response = new Response();
        $mesa = Mesa::obtenerMesaPorID($idMesa);
        if($mesa){
            if($mesa->estado !== "cerrada"){
                return $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(["error" => "La mesa esta cerrada"]));
            }
        }else{
            $response->getBody()->write(json_encode(["error" => "Mesa no encontrada"]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}