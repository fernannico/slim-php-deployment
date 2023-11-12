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

    public function CambiarEstadoPedido($estado)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE codigoAN = :codigoAN");
        $consulta->bindParam(":estado", $estado);
        $consulta->bindParam(":codigoAN", $this->codigoAN);
        return $consulta->execute();
    }
    
}