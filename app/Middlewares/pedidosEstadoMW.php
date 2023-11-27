<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Pedido.php';

class pedidosEstadoMW
{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idPedido = $parametros["idPedido"];

        $estado = Pedido::RetornarEstado($idPedido);
        // $pedido = Pedido::obtenerPedidosPorID($idPedido);
        // $estado = $pedido->estado;
        // var_dump($estado);
        if ($estado == "pendiente") {
            $mensajePrevio = "Estado cambiado a 'en preparacion'";//    json_encode(array("mensaje" => "Estado cambiado a 'en preparacion'"));
        }elseif ($estado == "listo para servir") {
            $mensajePrevio = "el pedido ya esta listo para servir";
        }elseif ($estado == "entregado") {
            $mensajePrevio = "el pedido ya esta entregado";
        }
        // Fecha antes
        $before = date('Y-m-d H:i:s');

        // Almacena datos previos en un array asociativo
        $data = [
            'mensajePrevio' => $mensajePrevio,
            'inicio preparado' => $before,
        ];

        // Continuar al controlador
        $response = $handler->handle($request);

        // respuesta del cll
        $responseData = json_decode($response->getBody());

        if ($responseData !== null) {
            // Almacena datos adicionales en el array existente
            $data['mensaje'] = $responseData->mensaje;
            $data['finalizado'] = date('Y-m-d H:i:s');
        }
        // Crea una nueva respuesta con el JSON creado manualmente
        $newResponse = new Response();
        $newResponse->getBody()->write(json_encode($data));

        return $newResponse->withHeader('Content-Type', 'application/json');
    }

}