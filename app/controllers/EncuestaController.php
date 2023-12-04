<?php
require_once './models/Encuesta.php';
require_once './models/Pedido.php';
class EncuestaController{
    public function CargarEncuesta($request, $response, $args){
        $parametros = $request->getParsedBody();

        if(isset($parametros['idMesa']) && !empty($parametros['idMesa']) && isset($parametros['codigoAN']) && !empty($parametros['codigoAN']) && isset($parametros['puntuacionMesa']) && !empty($parametros['puntuacionMesa']) && isset($parametros['puntuacionRestaurante']) && !empty($parametros['puntuacionRestaurante']) && isset($parametros['puntuacionMozo']) && !empty($parametros['puntuacionMozo']) && isset($parametros['puntuacionCocinero']) && !empty($parametros['puntuacionCocinero']) && isset($parametros['comentario']) && !empty($parametros['comentario']) )
        {
            $codigoAN = $parametros['codigoAN'];
            $puntuacionMesa = $parametros['puntuacionMesa'];
            $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
            $puntuacionMozo = $parametros['puntuacionMozo'];
            $puntuacionCocinero = $parametros['puntuacionCocinero'];
            $promedioPuntaje = ($puntuacionMesa + $puntuacionRestaurante + $puntuacionMozo + $puntuacionCocinero) /4;
                // var_dump($promedioPuntaje);

            $encuesta = new Encuesta();
            $encuesta->idMesa = $parametros['idMesa'];
            $encuesta->codigoAN = $codigoAN;
            $pedidos = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
            $pedido = $pedidos[0];
            $encuesta->nombreCliente = $pedido->nombreCliente;
            $encuesta->puntuacionMesa = $puntuacionMesa;
            $encuesta->puntuacionRestaurante = $puntuacionRestaurante;
            $encuesta->puntuacionMozo = $puntuacionMozo;
            $encuesta->puntuacionCocinero = $puntuacionCocinero;
            $encuesta->promedioPuntaje = $promedioPuntaje;
            $encuesta->comentario = $parametros['comentario'];
            Encuesta::CrearEncuesta($encuesta);
            $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));
        }else{
            $payload = json_encode(array("Error" => "Faltan parametros"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ObtenerMejoresComentariosController($request, $response, $args)
    {
        $lista = Encuesta::ObtenerMejoresComentarios();
        
        $payload = json_encode(array("Mejores_Comentarios:" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }
/*
    public function ObtenerEncuestas($request, $response, $args)
    {
        $lista = Encuesta::TraerEncuestas();

        $payload = json_encode($lista);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }

    public function ObtenerUnaEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $encuesta = Encuesta::TraerPorId($id);
        $payload = json_encode($encuesta);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
*/

}
