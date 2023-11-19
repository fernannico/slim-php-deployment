<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMW
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
                AutentificadorJWT::VerificarToken($token);
                $response = $handler->handle($request);
            }else{
                throw new Exception('Token no válido');
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}