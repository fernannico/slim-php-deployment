<?php

class Pedido
{
    public $codigoAN;
    public $idMozo;
    public $idMesa;
    public $productos;
    public $tiempoFinalizacion;
    public $estado;

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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoAN, idMozo, idMesa, productos, tiempoFinalizacion, estado) VALUES (:codigoAN, :idMozo, :idMesa, :productos, :tiempoFinalizacion, :estado)");

        $consulta->bindParam(':codigoAN', $this->codigoAN);
        $consulta->bindParam(':idMozo', $this->idMozo);
        $consulta->bindParam(':idMesa', $this->idMesa);
        $consulta->bindParam(':productos', $this->productos);
        $consulta->bindParam(':tiempoFinalizacion', $this->tiempoFinalizacion);
        $consulta->bindParam(':estado', $this->estado);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    /*
    public static function obtenerPedidoPorID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = $id");
        // $consulta->bindValue(1, $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $pedidoBuscado = $consulta->fetchObject('Pedido');
        return $pedidoBuscado;
    }
    */

    public static function obtenerTodosPedidos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function actualizarEstado($codigoAN, $estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE codigoAN = :codigoAN");
        $consulta->bindParam(":estado", $estado);
        $consulta->bindParam(":codigoAN", $codigoAN);
        $consulta->execute();
    }

    public static function RetornarEstado($codigoAN)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT estado FROM pedidos WHERE codigoAN = :codigoAN");
        $consulta->bindParam(":codigoAN", $codigoAN);
        $consulta->execute();
    
        // Obtener el estado del pedido
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return $resultado['estado']; // Devolver el estado
        } else {
            return "No se encontró el pedido"; // Manejo de error si no se encuentra el pedido
        }
    }
    
    public static function CambiarEstadoPedido($codigoAN,$tiempoFinalizacion)
    {
        $estado = Pedido::RetornarEstado($codigoAN);

        if ($estado === "pedido") {
            Pedido::actualizarEstado($codigoAN, "en preparacion");
            // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'en preparacion'"));
        } elseif ($estado === "en preparacion") {
            sleep($tiempoFinalizacion);
            Pedido::actualizarEstado($codigoAN, "listo para servir");
            // $retorno = json_encode(array("mensaje" => "Estado cambiado a 'finalizado'"));
        }
        // return $retorno;
        //no tiene sentido poner los mensajes aca porque solo salen los mensajes del controller
    }



}

?>