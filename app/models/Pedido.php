<?php

class Pedido
{
    public $idPedido;
    public $codigoAN;
    public $nombreCliente;
    public $idMozo;
    public $idMesa;
    public $idProducto;
    public $tiempoIniciado; //el momento en q el cocinero toma el pedido
    public $tiempoEstimado; //el pasado por parametro
    public $tiempoFinalizacion; //en el momento en que el cocinero finaliza el pedido
    public $imagenMesa;
    public $estado;         //agregar estado "borrado" a las opciones


    public static function generarCodigoAleatorio()
    {
        $caracteresPermitidos = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $codigoAleatorio = '';

        $longitudCaracteres = strlen($caracteresPermitidos);
        for ($i = 0; $i < 6; $i++) {
            $indice = rand(0, $longitudCaracteres - 1);
            $codigoAleatorio .= $caracteresPermitidos[$indice];
        }

        return $codigoAleatorio;
    }

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoAN, nombreCliente, idMozo, idMesa, idProducto, tiempoIniciado, tiempoEstimado, tiempoFinalizacion, imagenMesa, estado) VALUES (:codigoAN, :nombreCliente, :idMozo, :idMesa, :idProducto, :tiempoIniciado, :tiempoEstimado, :tiempoFinalizacion, :imagenMesa, :estado)");

        $consulta->bindParam(':codigoAN', $this->codigoAN);
        $consulta->bindParam(':nombreCliente', $this->nombreCliente);
        $consulta->bindParam(':idMozo', $this->idMozo);
        $consulta->bindParam(':idMesa', $this->idMesa);
        $consulta->bindParam(':idProducto', $this->idProducto);
        $consulta->bindParam(':tiempoIniciado', $this->tiempoIniciado);
        $consulta->bindParam(':tiempoEstimado', $this->tiempoEstimado);
        $consulta->bindParam(':tiempoFinalizacion', $this->tiempoFinalizacion);
        $consulta->bindParam(':imagenMesa', $this->imagenMesa);
        $consulta->bindParam(':estado', $this->estado);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function GuardarImagen($nombreImagen,$directorioDestino) {
        $retorno = false;
        $carpeta_archivos = $directorioDestino;

        $nombre_archivo = "pedido-".$this->codigoAN . "_mesa-" . $this->idMesa . ".jpg";       
        $ruta_destino = $carpeta_archivos . $nombre_archivo;

        if (move_uploaded_file($nombreImagen,  $ruta_destino)){
            $retorno = true;
        }     
        return $retorno;
    }

    public static function MoverImagen($nombreImagen,$carpetaOrigen,$directorioDestino)           //si se borra el pedido o si se paga
    {
        $retorno = false;

        $rutaOrigen = $carpetaOrigen . $nombreImagen;
        $rutaDestino = $directorioDestino . $nombreImagen;

        if (file_exists($rutaOrigen)) {
            try {
                if (rename($rutaOrigen, $rutaDestino)) {
                    $retorno = true;
                }
            } finally {
                $retorno = false;
            }
        }
    
        return $retorno;
    }

    public static function obtenerPedidoPorID($id)
    {
        //idPedido, codigoAN, nombreCliente, idMozo, idMesa, idProducto, tiempoFinalizacion, imagenMesa, estado
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE idPedido = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $productoBuscado = $consulta->fetchObject('Pedido');
        return $productoBuscado;   
    }

    public static function obtenerTodosPedidos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedidosPendientesPorSector($sector)
    {
        try {
            //code...
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos JOIN productos ON pedidos.idProducto = productos.id WHERE productos.sector = :sector AND pedidos.estado = 'pendiente'");
            $consulta->bindParam(":sector", $sector);
            $consulta->execute();
            $retorno = $consulta->fetchAll(PDO::FETCH_ASSOC);  
        } catch (\Throwable $th) {
            $retorno = false;
        }      
        return $retorno;
    }
    
    public static function obtenerTodosPedidosPendientes()
    {
        try {
            //code...
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos JOIN productos ON pedidos.idProducto = productos.id WHERE pedidos.estado = 'pendiente'");
            // $consulta->bindParam(":sector", $sector);
            $consulta->execute();
            $retorno = $consulta->fetchAll(PDO::FETCH_ASSOC);  
        } catch (\Throwable $th) {
            $retorno = false;
        }      
        return $retorno;
    }
    public static function RetornarEstado($id)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT estado FROM pedidos WHERE idPedido = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
    
        // Obtener el estado del pedido
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return $resultado['estado']; // Devolver el estado
        } else {
            return "No se encontró el pedido"; // Manejo de error si no se encuentra el pedido
        }
    }

    public static function actualizarEstado($id, $estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE idPedido = :id");
        $consulta->bindParam(":estado", $estado);
        $consulta->bindParam(":id", $id);
        $consulta->execute();
    }
    public static function actualizarTiempoIniciadoEstimado($id,$tiempoEstimado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $tiempoIniciado = date('Y-m-d H:i:s'); 
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET tiempoEstimado = :tiempoEstimado, tiempoIniciado = :tiempoIniciado WHERE idPedido = :id");
        $consulta->bindParam(":tiempoEstimado", $tiempoEstimado);
        $consulta->bindParam(":tiempoIniciado", $tiempoIniciado);
        $consulta->bindParam(":id", $id);
        $consulta->execute();
    }
    public static function actualizarTiempoFinalizacion($id,$tiempoFinalizacion)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET tiempoFinalizacion = :tiempoFinalizacion WHERE idPedido = :id");
        $consulta->bindParam(":tiempoFinalizacion", $tiempoFinalizacion);
        $consulta->bindParam(":id", $id);
        $consulta->execute();
    }

    public static function CambiarEstadoPedidoPorId($id)
    {
        $estado = Pedido::RetornarEstado($id);

        if ($estado === "pendiente") {
            Pedido::actualizarEstado($id, "en preparacion");
            // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'en preparacion'"));
        } elseif ($estado === "en preparacion") {
            // sleep($tiempoFinalizacion);
            Pedido::actualizarEstado($id, "listo para servir");
            // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'finalizado'"));
        }
        // return $retorno;
        //no tiene sentido poner los mensajes aca porque solo salen los mensajes del controller
    }
    public static function ObtenerCodigoANMesa($idMesa)
    {
        $codigoAN = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigoAN FROM pedidos WHERE idMesa = :idMesa");
        $consulta->bindParam(":idMesa", $idMesa);
        $consulta->execute();
        
        $codigoAN = $consulta->fetchColumn();
        return $codigoAN;

    }

    public static function obtenerPedidosPorCodigoAN($codigoAN)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigoAN = :codigoAN");
        $consulta->bindParam(":codigoAN", $codigoAN);
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        
    }
    
    public static function ObtenerPedidosListos()
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
        
        return $pedidosListosParaServir;
    }
    
    public static function ActualizarImagenMesaPedido($codigoAN,$imagenMesa)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET imagenMesa = :imagenMesa WHERE codigoAN = :codigoAN");
        $consulta->bindParam(":imagenMesa", $imagenMesa);
        $consulta->bindParam(":codigoAN", $codigoAN);
        $consulta->execute();
    }

}

?>