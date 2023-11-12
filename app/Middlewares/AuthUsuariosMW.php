<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMWSector
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        if($request->getMethod() === "POST") {
            $parametros = $request->getParsedBody();
        }else if($request->getMethod() === "GET") {
            $parametros = $request->getQueryParams();
        }

        $sector = $parametros['sector'];
        $puesto = $parametros['puesto'];

        $puestoesPermitidos = ["mozo", "cocinero", "bartender", "socio", "cervecero"];

        //validamos puesto
        if (in_array($puesto, $puestoesPermitidos)) {
            //validamos sector
            if ($sector === "barra" | $sector === "choperas" | $sector === "cocina" | $sector === "candy bar"| $sector === "mozos"| $sector === "socios") {
                //validamos sector con puesto
                if (($puesto == "cervecero" && $sector == "choperas") || ($puesto == "bartender" && $sector == "barra") || ($puesto == "mozo" && $sector == "mozos") || ($puesto == "socio" && $sector == "socios") || ($puesto == "cocinero" && in_array($sector, ["cocina", "candy bar"]))) {
                    //si es todo correcto, crea al usuario
                    $response = $handler->handle($request);
                } else {
                    $response = new Response();
                    $payload = json_encode(array("error" => "<br>La combinacion de puesto y sector no es valida, lo correcto es:<br>
                    <br>Puesto | Sector
                    <br>mozo -> mozos
                    <br>socio -> socios
                    <br>bartender -> barra
                    <br>cerveceros -> choperas
                    <br>cocineros -> cocina o candy bar"));
                    
                    $response->getBody()->write($payload);
                }   
            }else{
                $response = new Response();
                $payload = json_encode(array('mensaje' => 'Sector no identificado<BR>Sectores permitidosss:  barra, choperas, cocina, candy bar, mozos, socios'));
                $response->getBody()->write($payload);
            }
        }else{
            $response = new Response();
            $payload = json_encode(array("errror" => "Puesto no valido. Los puestos disponibles son: mozo, cocinero, bartender, socio, cervecero"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    
}