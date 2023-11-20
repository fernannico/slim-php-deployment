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

        // si la mesa esta en estado "pidiendo", obtener el código aleatorio para la misma mesa de ese pedido
        if ($estadoMesa === 'pidiendo') {
            $codigoAleatorio = Pedido::ObtenerCodigoANMesaPidiendo($idMesa);//lo pisa al codigoAN anterior
        }//si no esta en estado "pidiendo", el codigoAN es el nuevo creado antes
        
        // Creamos el pedido
        $usr = new Pedido();
        $usr->codigoAN = $codigoAleatorio;
        $usr->idMozo = $idMozo; 
        $usr->idMesa = $idMesa;
        $usr->idProducto = $idProducto;
        $usr->estado = "pendiente";
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

    public function TraerPedidosPendientesPorSectorController($request, $response, $args)
    {       
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
    
        $data = AutentificadorJWT::ObtenerData($token);
        // var_dump($data);
        // echo "<br>";
        // var_dump($data->sector);
        // echo "<br>";

        $listaPedidos = Pedido::obtenerPedidosPendientesPorSector($data->sector);
        $payload = json_encode(array("PedidosPendientes" => $listaPedidos));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    
    }

    public static function TomarPedidoController($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $idPedido = $parametros["idPedido"];
        $tiempoFinalizacion = $parametros["tiempoFinalizacion"];

        $estado = Pedido::RetornarEstado($idPedido);
        
        // ver si MW con si es pedido, pasar x segundos + mje y despues de esos segudos ir al header
        
        do {
            if ($estado == "pendiente") {
                Pedido::CambiarEstadoPedidoPorId($idPedido,$tiempoFinalizacion);
                Pedido::actualizarTiempoFinalizacion($idPedido,$tiempoFinalizacion);    //cambiar tiempo de finalizacion
                // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'en preparacion'"));
                //este mensaje nunca va a aparecer desde el controller
            } else if($estado === "en preparacion"){
                Pedido::CambiarEstadoPedidoPorId($idPedido,$tiempoFinalizacion);
                $retorno = json_encode(array("mensaje" => "Estado cambiado a 'listo para servir'"));
            } else if($estado === "listo para servir") {
                $retorno = json_encode(array("mensaje" => "el pedido ya esta listo para servir"));
            }
            $estado = Pedido::RetornarEstado($idPedido);
        }while($estado !== "listo para servir");

        $response->getBody()->write($retorno);
        return $response;
    }

    public static function obtenerPedidosListosParaServirController($request, $response, $args)
    {
        $listaPedidos = Pedido::obtenerTodosPedidos(); // Obtener todos los pedidos

        // Agrupar pedidos por codigoAN
        $pedidosPorCodigo = [];
        foreach ($listaPedidos as $pedido) {
            $pedidosPorCodigo[$pedido->codigoAN][] = $pedido;
        }

        // Filtrar los codigoAN que tienen al menos un pedido que no está listo para servir
        $codigoANListosParaServir = [];
        foreach ($pedidosPorCodigo as $codigo => $pedidos) {
            $todosListosParaServir = true;
            foreach ($pedidos as $pedido) {
                if ($pedido->estado !== "listo para servir") {
                    $todosListosParaServir = false;
                    break;
                }
            }
            if ($todosListosParaServir) {
                $codigoANListosParaServir[] = $codigo;
            }
        }

        // Crear una lista de objetos con codigoAN y estado "listo para servir"
        $pedidosListosParaServir = [];
        foreach ($codigoANListosParaServir as $codigo) {
            $pedido = new stdClass();
            $pedido->codigoAN = $codigo;
            $pedido->estado = "listo para servir"; // Puedes asignar directamente este estado si todos son "listo para servir"
            $pedidosListosParaServir[] = $pedido;
        }

        $payload = json_encode(array("listaPedido" => $pedidosListosParaServir));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /*public static function EntregarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigoAN = $parametros["codigoAN"];

        Pedido::actualizarEstado($id, "entregado");
        Mesa::actualizarEstado($idMesa,"con cliente comiendo");

        $retorno = json_encode(array("mensaje" => "pedido " .$codigoAN . " entregado"));

        $response->getBody()->write($retorno);
        return $response;
    }*/
    public static function EntregarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigoAN = $parametros["codigoAN"];

        $pedidosConCodigoAN = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
        // var_dump($pedidosConCodigoAN);

        foreach ($pedidosConCodigoAN as $pedido) {
            // var_dump($pedido);
            $id = $pedido->idPedido;
            Pedido::actualizarEstado($id,"entregado");
        }

        $retorno = json_encode(array("mensaje" => "Pedidos con codigoAN " . $codigoAN . " entregados"));

        $response->getBody()->write($retorno);
        return $response;
    }

}

?>