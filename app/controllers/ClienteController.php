<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';
// require_once './interfaces/IApiUsable.php';

use Illuminate\Support\Facades\Date;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ClienteController
{
    //Las mesas tienen un código de identificación único (de 5 caracteres) , el cliente al entrar en nuestra aplicación puede ingresar ese código junto con el número del pedido y se le mostrará el tiempo restante para su pedido.   
    public static function ObtenerTiempoRestantePedidoController($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['idMesa']) && isset($queryParams['idPedido']) && !empty($queryParams['idMesa']) && !empty($queryParams['idPedido'])) {
            $idMesa = $queryParams['idMesa'];
            $idPedido = $queryParams["idPedido"];

            $pedido = Pedido::obtenerPedidoPorID($idPedido);
            if ($pedido) {
                if($pedido->idMesa == $idMesa)
                {
                    $tiempoInicioDT = new DateTime($pedido->tiempoIniciado);
                    // var_dump($tiempoInicioDT);

                    // $tiempoEstimado = DateInterval::createFromDateString($pedido->tiempoEstimado); // Tiempo estimado 30 minutos (PT = Period Time)
                    $parts = explode(':', $pedido->tiempoEstimado);
                    $tiempoEstimado = new DateInterval('PT' . $parts[0] . 'H' . $parts[1] . 'M' . $parts[2] . 'S');
                    // var_dump($tiempoEstimado);
        
                    $tiempoFinalEstimado = clone $tiempoInicioDT;
                    $tiempoFinalEstimado->add($tiempoEstimado);
                    // var_dump($tiempoFinalEstimado);

                    $tiempoActual = new DateTime();
                    // var_dump($tiempoActual);
                    if ($tiempoActual < $tiempoFinalEstimado) {
                        $tiempoRestante = $tiempoActual->diff($tiempoFinalEstimado); // Obtiene la diferencia entre las fechas
                        $horasRestantes = $tiempoRestante->format('%h'); // Obtener las horas restantes
                        $minutosRestantes = $tiempoRestante->format('%i'); // Obtener los minutos restantes
                        $segundosRestantes = $tiempoRestante->format('%s'); // Obtener los segundos restantes
                        $retorno = json_encode(array("Preparando" => $horasRestantes . ":" .$minutosRestantes . ":" . $segundosRestantes));
                    } else {
                        $retorno = json_encode(array("Problema" => "el pedido esta demorado"));
                    }
                }else {
                    $retorno = json_encode(array("Error" => "No coinciden el id del pedido con el id de la mesa"));
                }
            }else {
                $retorno = json_encode(array("Error" => "No existe dicho pedido"));
            }
        } else {
            $retorno = json_encode(array("mensaje" => "faltan parametros"));
        }

        $response->getBody()->write($retorno);

        return $response;
    }

}