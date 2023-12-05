<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';

class AuthMailMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {        
        $parametros = $request->getParsedBody();
        $response = new Response();
        if(isset($parametros['mail']) && !empty($parametros['mail'])){
            $mail = $parametros['mail'];
    
            if ($this->existeMail($mail)) {
                $response->getBody()->write(json_encode(["error" => "el correo electronico ya esta registrado"]));
            }else{
                $response = $handler->handle($request);
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }        
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function existeMail($mail)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) AS cantidad FROM usuarios WHERE mail = :mail");
            $consulta->bindParam(':mail', $mail);
            $consulta->execute();
        
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
            return $resultado['cantidad'] > 0;
        } catch (PDOException $e) {
            // Manejar el error, por ejemplo:
            error_log("Error en la consulta: " . $e->getMessage());
            return true; // En este caso, se asume que hay un problema en la consulta, por lo que se impide la solicitud.
        }
    }
}

