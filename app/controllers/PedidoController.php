<?php
require_once './models/Pedido.php';
// require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido /*implements IApiUsable*/
{
    public function CargarPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMozo = $parametros['idMozo'];
        $idMesa = $parametros['idMesa'];
        $productos = $parametros['productos'];
        $tiempoFinalizacion = $parametros['tiempoFinalizacion'];
        $estado = $parametros['estado'];

        //Codigo aleatorio
        $codigoAleatorio = Pedido::generarCodigoAleatorio();
        
        // Creamos el pedido
        $usr = new Pedido();
        $usr->codigoAN = $codigoAleatorio;
        $usr->idMozo = $idMozo;
        $usr->idMesa = $idMesa;
        $usr->productos = $productos;
        $usr->tiempoFinalizacion = $tiempoFinalizacion;
        $usr->estado = $estado;
        $usr->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /*
    public function TraerUno($request, $response, $args)
    {
        // Buscamos pedido por id
        $id = $args['id'];
        $pedido = Pedido::obtenerPedidoPorID($id);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    */
    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodosPedidos();
        $payload = json_encode(array("listaPedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}