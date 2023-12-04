<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthSectorMW
{
    public $sector;
    public function __construct($sector)
    {
        $this->sector = $sector;
    }
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);

        if($data->sector === $this->sector || $data->sector === 'socios'){
            $request = $request->withAttribute('datosToken', $data);//retorna en el request la data del token
            $response = $handler->handle($request);
        }else{
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: usuario no autorizado, tiene que ser del sector ' . $this->sector));
            $response->getBody()->write($payload);
        }
            
        return $response->withHeader('Content-Type', 'application/json');
    }
}