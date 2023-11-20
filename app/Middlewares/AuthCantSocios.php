<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';

class AuthCantSocios
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {        
        try {
            $parametros = $request->getParsedBody();
            $tipoSector = $parametros['sector'];

            // Verifica si el tipo de sector es "socios" para permitir la validación
            if ($tipoSector === "socios") {
                $cantidadUsuariosSocios = self::obtenerCantidadSocios($tipoSector);

                if ($cantidadUsuariosSocios < 3) {
                    $response = $handler->handle($request);
                } else {
                    throw new Exception('Cantidad máxima de usuarios con sector "socios" alcanzada');
                }
            } else {
                $response = $handler->handle($request);
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => $e->getMessage()));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }    

    private static function obtenerCantidadSocios($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) AS cantidad FROM usuarios WHERE sector = :sector");
        $consulta->bindParam(":sector", $sector);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado['cantidad'] ?? 0; // Devuelve la cantidad encontrada o 0 si no hay resultados
    }
}

