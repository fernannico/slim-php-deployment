<?php
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
// require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido /*implements IApiUsable*/
{
    public function CargarPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['nombreCliente']) && !empty($parametros['nombreCliente'])){
            $dataToken = $request->getAttribute('datosToken');

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
            $usr->tiempoIniciado = "0000-00-00 00:00:00";
            $usr->tiempoEstimado = "00:00:00";
            $usr->tiempoFinalizacion = "0000-00-00 00:00:00";
            $usr->imagenMesa = "sin imagen";
            $usr->estado = "pendiente";
            $usr->crearPedido();
            Mesa::actualizarEstado($idMesa,"pidiendo");
    
            $payload = json_encode(array("mensaje" => "Pedido creado con exito"));    
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }

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
            
            if($listaPedidos && !empty($listaPedidos)){
                $payload = json_encode(array("Todos_Pedidos_Pendientes" => $listaPedidos));
            }else{
                $payload = json_encode(array("Pedidos" =>"No hay pedidos pendientes"));
            }
        }else{
            $listaPedidos = Pedido::obtenerPedidosPendientesPorSector($sector);

            if($listaPedidos && !empty($listaPedidos)){
                $payload = json_encode(array("PedidosPendientes" => $listaPedidos));
            }else{
                $payload = json_encode(array("Pedidos" =>"No hay pedidos pendientes"));
            }
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //estado "pendiente" a "en preparacion"
    public static function TomarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros["idPedido"]) && !empty($parametros["idPedido"]) && isset($parametros["tiempoEstimado"]) && !empty($parametros["tiempoEstimado"]) )
        {
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $data = AutentificadorJWT::ObtenerData($token);
            $sectorLogin = $data->sector;
    
            if($sectorLogin == "mozos"){
                //caso mozo
                $retorno = json_encode(array("PedidosEnPreparacion" => "El mozo no puede tomar un pedido pendiente"));
            }else{
                $idPedido = $parametros["idPedido"];
                $pedidoAPreparar = Pedido::obtenerPedidoPorID($idPedido);
                $idProducto = $pedidoAPreparar->idProducto;
                $productoAPreparar = Producto::ObtenerProductoPorID($idProducto);

                if($pedidoAPreparar){
                    if($productoAPreparar->sector === $sectorLogin || $sectorLogin === "socios"){
                        if($pedidoAPreparar->estado == "pendiente"){
                            $tiempoEstimado = $parametros["tiempoEstimado"];
                            $tiempoEstimado = gmdate("H:i:s", $tiempoEstimado * 60); // Convertir minutos a formato H:i:s
                            Pedido::actualizarTiempoIniciadoEstimado($pedidoAPreparar->idPedido,$tiempoEstimado);
                            Pedido::CambiarEstadoPedidoPorId($pedidoAPreparar->idPedido);
                            Mesa::actualizarEstado($pedidoAPreparar->idMesa,"con cliente esperando pedido");
                            $retorno = json_encode(array("mensaje" => "pedido tomado con exito"));   
                        }else{
                            $retorno = json_encode(array("mensaje" => "pedido no tomado porque tiene estado " . $pedidoAPreparar->estado));   
                        }
                    }else{
                        $retorno = json_encode(array("error" => "pedido NO tomado porque el usuario es del sector " . $data->sector . " y el pedido pertenece a " . $productoAPreparar->sector));   
                    }
                }else{
                    $retorno = json_encode(array("mensaje" => "el pedido no existe"));   
                }  
            }
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
        }
        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerPedidosEnPreparacionPorSectorController($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
    
        $data = AutentificadorJWT::ObtenerData($token);
        $sector = $data->sector;
        $listaPedidos = Pedido::obtenerTodosPedidosEnPreparacion();
        // var_dump($listaPedidos);
        if($sector == "mozos"){
            //caso mozo
            $payload = json_encode(array("PedidosEnPreparacion" => "El mozo no puede ver los pedidos En Preparacion"));
        }elseif($sector == "socios"){
            //caso socio
            if($listaPedidos && !empty($listaPedidos)){
                $payload = json_encode(array("Todos_Pedidos_EnPreparacion" => $listaPedidos));
            }else{
                $payload = json_encode(array("Pedidos" =>"No hay pedidos En Preparacion"));
            }
        }else{
            $pedidosPorSector = Array();
            foreach($listaPedidos as $pedido)
            {
                $sectorProducto = $pedido['sector'];
                if($sectorProducto === $sector){
                    $pedidosPorSector[] = $pedido;
                }
            }
            // var_dump($pedidosPorSector);

            if($pedidosPorSector && !empty($pedidosPorSector)){
                $payload = json_encode(array("PedidosEnPreparacion" => $pedidosPorSector));
            }else{
                $payload = json_encode(array("Pedidos" =>"No hay pedidos En Preparacion"));
            }
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //estado "en preparacion" a "listo para servir"
    public static function FinalizarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        // $idPedido = $parametros["idPedido"];
        $tiempoActual = date('Y-m-d H:i:s');

        if(isset($parametros["idPedido"]) && !empty($parametros["idPedido"]))
        {
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $data = AutentificadorJWT::ObtenerData($token);
            $sectorLogin = $data->sector;
    
            if($sectorLogin == "mozos"){
                //caso mozo
                $retorno = json_encode(array("PedidosEnPreparacion" => "El mozo no puede finalizar un pedido en preparacion"));
            }else{
                $idPedido = $parametros["idPedido"];
                $pedidoAFinalizar = Pedido::obtenerPedidoPorID($idPedido);
                $idProducto = $pedidoAFinalizar->idProducto;
                $productoAFinalizar = Producto::ObtenerProductoPorID($idProducto);

                if($pedidoAFinalizar){
                    if($productoAFinalizar->sector === $sectorLogin || $sectorLogin === "socios"){
                        if($pedidoAFinalizar->estado == "en preparacion"){
                            Pedido::actualizarTiempoFinalizacion($pedidoAFinalizar->idPedido,$tiempoActual);                //puede ser de clase
                            Pedido::CambiarEstadoPedidoPorId($pedidoAFinalizar->idPedido);
                            // Mesa::actualizarEstado($pedidoAFinalizar->idMesa,"con cliente esperando pedido");
                            $retorno = json_encode(array("mensaje" => "pedido finalizado con exito"));   
                        }else{
                            $retorno = json_encode(array("mensaje" => "pedido no finalizado, tiene que tener el estado 'en preparacion', pero tiene estado " . $pedidoAFinalizar->estado));   
                        }
                    }else{
                        $retorno = json_encode(array("error" => "pedido NO tomado porque el usuario es " . $data->sector . " y el pedido pertenece a " . $productoAFinalizar->sector));   
                    }
                }else{
                    $retorno = json_encode(array("error" => "el pedido no existe"));   
                }  
 
            }
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
        }
        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
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

    public static function EntregarPedidoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['codigoAN']) && !empty($parametros['codigoAN'])){
            $codigoAN = $parametros["codigoAN"];

            $pedidosConCodigoAN = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
            // var_dump($pedidosConCodigoAN);
            
            //validar que el codigoAN pasado esten todos los subpedidos en listo para servir
            $todosListos = true;
            foreach($pedidosConCodigoAN as $pedido){
                // var_dump($todosListos);
                if($pedido->estado === "entregado"){
                    $todosListos = "entregado";
                    break;
                }elseif($pedido->estado === "cobrado"){
                    $todosListos = "cobrado";
                    break;
                }elseif($pedido->estado != "listo para servir"){
                    $todosListos = false;
                    break;
                }
            }
            // var_dump($todosListos);
    
            if($todosListos === "entregado"){
                $retorno = json_encode(array("error" => "el pedido ya fue entregado"));   
            }elseif($todosListos === "cobrado"){
                $retorno = json_encode(array("error" => "el pedido ya fue cobrado"));   
            }elseif($todosListos === true){
                foreach ($pedidosConCodigoAN as $pedido) {
                    // var_dump($pedido);
                    $id = $pedido->idPedido;
                    Pedido::actualizarEstado($id,"entregado");
                }
                Mesa::actualizarEstado($pedido->idMesa,"con cliente comiendo");
        
                $retorno = json_encode(array("mensaje" => "Pedidos con codigoAN " . $codigoAN . " entregados"));    
            }else{
                $retorno = json_encode(array("error" => "Faltan finalizar pedidos de este codigo alfanumerico"));   
            }    
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
            // $response->getBody()->write($payload);
        }

        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TomarFotoPedidoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];                                //35424                                           
        $mesa = Mesa::obtenerMesaPorID($idMesa);
        if(isset($_FILES['imagen']) && !empty($_FILES['imagen'])){
            $nombreImagen = $_FILES['imagen']['tmp_name'];
            $codigoAN = Pedido::ObtenerCodigoANMesa($idMesa); 
            $pedidos = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
            $pedido = $pedidos[0];
            if(!$pedidos){
                $payload = json_encode(array("error" => "la mesa no cuenta con un pedido asociado "));
            }else{
                if($pedido->estado == "cobrado"){
                    $payload = json_encode(array("error" => "El pedido ya fue cobrado y la mesa liberada"));
                }else{
                    if($pedido){
                        $mesa = Mesa::obtenerMesaPorID($idMesa);
                        $directorioImagenesAlta = "ImagenesDePedidos/";
                        
                        if($pedido->GuardarImagen($nombreImagen,$directorioImagenesAlta)){
                            $nombre_archivo = "pedido-".$pedido->codigoAN . "_mesa-" . $pedido->idMesa . ".jpg";       
                            $pedido->ActualizarImagenMesaPedido($codigoAN,$nombre_archivo);
                            // var_dump($pedido); 
                            $mesa->ActualizarImagenMesaPedido($nombre_archivo);
                            $payload = json_encode(array("mensaje" => "foto relacionada con exito "));
                        }else{
                            $payload = json_encode(array("mensaje" => "no se pudo  relacionar la foto "));
                        }                
                    }              
                }
            }
        }else{
            $payload = json_encode(array("mensaje" => "falta el parametro de la imagen "));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');

    }
}

?>