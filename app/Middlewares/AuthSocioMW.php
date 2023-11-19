<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';

class AuthSocioMW
{
    /*
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idUsuario = $parametros["idUsuario"];

        $usuario = Usuario::obtenerUsuarioPorID($idUsuario);
        $puesto = $usuario->puesto;

        if ($puesto === 'socio') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "No sos socio"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    
        // if(!isset($param['token'])){
        //     $retorno = json_encode(array("mensaje" => "Token necesario"));
        // }
        // else{
            // $token = $param['token'];
            // $respuesta = Autenticador::ValidarToken($token, "Admin");
            // if($respuesta == "Validado"){
                
            // }
            // else{
            //     $retorno = json_encode(array("mensaje" => $respuesta));
            // }
        // }
}