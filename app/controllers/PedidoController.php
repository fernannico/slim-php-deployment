<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';
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
        $nombreCliente = $parametros['nombreCliente'];
        $idProducto = $parametros['idProducto'];
        $codigoAleatorio = Pedido::generarCodigoAleatorio();

        //estado de la mesa
        $estadoMesa = Mesa::ObtenerEstadoPorID($idMesa);

        // si la mesa esta en estado "pidiendo", obtener el código aleatorio para la misma mesa de ese pedido
        if ($estadoMesa === 'pidiendo') {
            $codigoAleatorio = Pedido::ObtenerCodigoANMesa($idMesa);//lo pisa al codigoAN anterior
        }//si no esta en estado "pidiendo", el codigoAN es el nuevo creado antes
        
        // Creamos el pedido
        $usr = new Pedido();
        $usr->codigoAN = $codigoAleatorio;
        $usr->idMozo = $idMozo; 
        $usr->nombreCliente = $nombreCliente; 
        $usr->idMesa = $idMesa;
        $usr->idProducto = $idProducto;
        $usr->imagenMesa = "sin imagen";
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
        $sector = $data->sector;
        
        if($sector == "mozos"){
            //caso mozo
            $payload = json_encode(array("PedidosPendientes" => "El mozo no puede ver los pedidos pendientes"));
        }elseif($sector == "socios"){
            //caso socio
            $listaPedidos = Pedido::obtenerTodosPedidosPendientes();
            $payload = json_encode(array("Todos_Pedidos_Pendientes" => $listaPedidos));
        }else{
            $listaPedidos = Pedido::obtenerPedidosPendientesPorSector($sector);

            if($listaPedidos && !empty($listaPedidos)){
                $payload = json_encode(array("PedidosPendientes" => $listaPedidos));
            }else{
                $payload = json_encode(array("PedidosPendientes" =>"No hay pedidos"));
            }
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    
    }

    //estado "en preparacion"
    public static function TomarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idPedido = $parametros["idPedido"];
        $tiempoEstimado = $parametros["tiempoEstimado"];
        $tiempoEstimado = gmdate("H:i:s", $tiempoEstimado * 60); // Convertir minutos a formato H:i:s
        
        // $tiempoActual = date('Y-m-d H:i:s');
        //el t° finalizacion lo pone el cocinero cuando termina
        // $tiempoFinalizacion = date('Y-m-d H:i:s', strtotime($tiempoActual . " + $tiempoEstimado minutes"));

        $pedidoAPreparar = Pedido::obtenerPedidoPorID($idPedido);

        if($pedidoAPreparar){
            if($pedidoAPreparar->estado == "pendiente"){
                Pedido::actualizarTiempoIniciadoEstimado($pedidoAPreparar->idPedido,$tiempoEstimado);
                Pedido::CambiarEstadoPedidoPorId($pedidoAPreparar->idPedido);
                Mesa::actualizarEstado($pedidoAPreparar->idMesa,"con cliente esperando pedido");
                $retorno = json_encode(array("mensaje" => "pedido tomado con exito"));   
            }else{
                $retorno = json_encode(array("mensaje" => "pedido no tomado porque tiene estado " . $pedidoAPreparar->estado));   
            }
        }else{
            $retorno = json_encode(array("mensaje" => "el pedido no existe"));   
        }
        $response->getBody()->write($retorno);
        return $response;
    }

    public static function obtenerPedidosListosParaServirController($request, $response, $args)
    {
        $pedidosListosParaServir = Pedido::ObtenerPedidosListos();

        if($pedidosListosParaServir && !empty($pedidosListosParaServir)){
            $payload = json_encode(array("listaPedido" => $pedidosListosParaServir));
        }else{
            $payload = json_encode(array("listaPedido" => "No hay pedidos listos para servir"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function FinalizarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idPedido = $parametros["idPedido"];
        // $tiempoEstimado = $parametros["tiempoEstimado"];
        $tiempoActual = date('Y-m-d H:i:s');
        //el t° finalizacion lo pone el cocinero cuando termina
        // $tiempoFinalizacion = date('Y-m-d H:i:s', strtotime($tiempoActual . " + $tiempoEstimado minutes"));

        $pedidoAFinalizar= Pedido::obtenerPedidoPorID($idPedido);

        if($pedidoAFinalizar){
            if($pedidoAFinalizar->estado == "en preparacion"){
                // Pedido::actualizarTiempoIniciadoEstimado($pedidoAPreparar->idPedido,$tiempoEstimado);
                Pedido::actualizarTiempoFinalizacion($pedidoAFinalizar->idPedido,$tiempoActual);                //puede ser de clase
                Pedido::CambiarEstadoPedidoPorId($pedidoAFinalizar->idPedido);                                  //puede ser de clase
    
                $retorno = json_encode(array("mensaje" => "pedido finalizado!"));   
            }else{
                $retorno = json_encode(array("mensaje" => "pedido no finalizado porque tiene estado: " . $pedidoAFinalizar->estado));   
            }
        }else{
            $retorno = json_encode(array("mensaje" => "el pedido no existe"));   
        }
        $response->getBody()->write($retorno);
        return $response;
    }

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
        Mesa::actualizarEstado($pedido->idMesa,"con cliente comiendo");

        $retorno = json_encode(array("mensaje" => "Pedidos con codigoAN " . $codigoAN . " entregados"));

        $response->getBody()->write($retorno);
        return $response;
    }

    public static function TomarFotoPedidoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];                                                //verificar q existe el codigo de la mesa
        
        if(isset($_FILES['imagen']) && !empty($_FILES['imagen'])){
            $nombreImagen = $_FILES['imagen']['tmp_name'];
            $codigoAN = Pedido::ObtenerCodigoANMesa($idMesa); 
            $pedidos = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
            if(!$pedidos){
                $payload = json_encode(array("mensaje" => "la mesa no cuenta con un pedido asociado<BR>"));
            }else{
                $pedido = $pedidos[0];
                if($pedido){
                    $mesa = Mesa::obtenerMesaPorID($idMesa);
                    $directorioImagenesAlta = "ImagenesDePedidos/";
                    
                    if($pedido->GuardarImagen($nombreImagen,$directorioImagenesAlta)){
                        $nombre_archivo = "pedido-".$pedido->codigoAN . "_mesa-" . $pedido->idMesa . ".jpg";       
                        $pedido->ActualizarImagenMesaPedido($codigoAN,$nombre_archivo);
                        // var_dump($pedido); 
                        $mesa->ActualizarImagenMesaPedido($nombre_archivo);
                        $payload = json_encode(array("mensaje" => "foto relacionada con exito<BR>"));
                    }else{
                        $payload = json_encode(array("mensaje" => "no se pudo  relacionar la foto<BR>"));
                    }                
                }              
            }
        }else{
            $payload = json_encode(array("mensaje" => "falta el parametro de la imagen<BR>"));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');

    }
}

?>