<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Encuesta.php';

class AuthEncuestaHecha
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {        
        $parametros = $request->getParsedBody();
        $response = new Response();
        if(isset($parametros['codigoAN']) && !empty($parametros['codigoAN'])){
            $codigoAN = $parametros['codigoAN'];
    
            $encuesta = Encuesta::ObtenerEncuestaPorCodigoAN($codigoAN);
            if($encuesta){
                $response->getBody()->write(json_encode(["error" => "Ya se realizó la encuesta de ese pedido"]));
            }else{
                $response = $handler->handle($request);
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}