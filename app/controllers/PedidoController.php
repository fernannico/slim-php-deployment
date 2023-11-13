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

        //Codigo aleatorio
        $codigoAleatorio = Pedido::generarCodigoAleatorio();
        
        // Creamos el pedido
        $usr = new Pedido();
        $usr->codigoAN = $codigoAleatorio;
        $usr->idMozo = $idMozo;
        $usr->idMesa = $idMesa;
        $usr->productos = $productos;
        $usr->estado = "encargado";
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

    public static function ModificarEstado($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $codigoAN = $parametros["codigoAN"];
        $tiempoFinalizacion = $parametros["tiempoFinalizacion"];

        $estado = Pedido::RetornarEstado($codigoAN);
        
        // ver si MW con si es pedido, pasar x segundos + mje y despues de esos segudos ir al header
        
        do {
            if ($estado == "pedido") {
                Pedido::CambiarEstadoPedido($codigoAN,$tiempoFinalizacion);
                // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'en preparacion'"));
                //este mensaje nunca va a aparecer desde el controller
            } else if($estado === "en preparacion"){
                Pedido::CambiarEstadoPedido($codigoAN,$tiempoFinalizacion);
                $retorno = json_encode(array("mensaje" => "Estado cambiado a 'listo para servir'"));
            } else if($estado === "listo para servir") {
                $retorno = json_encode(array("mensaje" => "el pedido ya esta listo para servir"));
            }
            $estado = Pedido::RetornarEstado($codigoAN);
        }while($estado !== "listo para servir");

        $response->getBody()->write($retorno);
        return $response;
    }

    
}

?>