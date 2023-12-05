<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';

class AuthEstadoUsuarioMW
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
        try {
            $header = $request->getHeaderLine('Authorization');
            $token = '';
            if(!empty($header)) {
                $token = trim(explode("Bearer", $header)[1]);
                $datos = AutentificadorJWT::ObtenerData($token);

                if($datos->estado == "activo"){
                    $response = $handler->handle($request);
                }else{
                    $response = new Response();
                    $payload = json_encode(array('ERROR:' => 'el socio logeado esta con estado ' . $datos->estado));    
                    $response->getBody()->write($payload);
                }
            }else{
                throw new Exception('Token no valido');
            }
        } catch (Exception $e) {
            //throw $th;
            $response = new Response();
            $payload = json_encode(array('ERROR' => 'Token no valido'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    
    }
    
}