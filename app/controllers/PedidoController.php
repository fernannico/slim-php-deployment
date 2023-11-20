<?php
require_once './models/Pedido.php';
// require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido /*implements IApiUsable*/
{
    public function CargarPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken);

        $idMozo = $dataToken->id;
        $idMesa = $parametros['idMesa'];
        $idProducto = $parametros['idProducto'];
        $codigoAleatorio = Pedido::generarCodigoAleatorio();

        //estado de la mesa
        $estadoMesa = Mesa::ObtenerEstadoPorID($idMesa);

        // si la mesa esta en estado "pidiendo" Obtener el código aleatorio para la misma mesa de ese pedido
        if ($estadoMesa === 'pidiendo') {
            $codigoAleatorio = Pedido::ObtenerCodigoANMesaPidiendo($idMesa);//lo pisa al codigoAN anterior
        }//si no esta en estado "pidiendo", el codigoAN es el nuevo creado antes
        
        // Creamos el pedido
        $usr = new Pedido();
        $usr->codigoAN = $codigoAleatorio;
        $usr->idMozo = $idMozo; 
        $usr->idMesa = $idMesa;
        $usr->idProducto = $idProducto;
        $usr->estado = "encargado";
        $usr->crearPedido();
        Mesa::actualizarEstado($idMesa,"pidiendo");

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

    public static function ModificarEstadoController($request, $response, $args) {
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