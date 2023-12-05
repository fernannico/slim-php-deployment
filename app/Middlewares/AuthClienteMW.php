<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';

class AuthClienteMW
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
                if($datos->puesto === "cliente"){                 
                    $request = $request->withAttribute('datosToken', $datos);
                    $response = $handler->handle($request);
                }else{
                    throw new Exception('no es cliente');
                }
            }else{
                throw new Exception('Token no valido');
            }
        } catch (Exception $e) {
            //throw $th;
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: solo clientes autorizados'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    
    }
    
}