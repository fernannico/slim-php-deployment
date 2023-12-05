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
        try {
            $header = $request->getHeaderLine('Authorization');
            $token = '';
            if(!empty($header)) {
                $token = trim(explode("Bearer", $header)[1]);
                $datos = AutentificadorJWT::ObtenerData($token);

                if($datos->puesto === "socio"){                 
                    if($datos->estado == "activo"){
                        //$request->datosToken = $datos;              //el request/response es un objeto, y puedo agregarle/sacarle cosas. Asi que al request del controller le va a llegar lo del body/param y ahora sumado, lo que le paso como data
                                                                    //entonces puedo hacer un MW que me retorne al controller el tiepo de puesto mediante modificar el controller con la data
                        $request = $request->withAttribute('datosToken', $datos);//retorna en el request la data del token
                        $response = $handler->handle($request);
                    }else{
                    $response = new Response();
                    $payload = json_encode(array('ERROR:' => 'el socio logeado esta con estado ' . $datos->estado));    
                    $response->getBody()->write($payload);
                    }
                }else{
                    throw new Exception('no es socio');
                }
            }else{
                throw new Exception('Token no valido');
            }
        } catch (Exception $e) {
            //throw $th;
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: solo socios autorizados'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    
    }
    
}