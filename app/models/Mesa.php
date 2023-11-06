<?php

class Mesa
{
    public $id;
    public $estado;

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (id, estado) VALUES (:id, :estado)");

        $consulta->bindParam(':id', $this->id);
        $consulta->bindParam(':estado', $this->estado);
        // $consulta->bindParam(':nombre', $this->ingresoSist);
        // $consulta->bindParam(':nombre', $this->cantOperaciones);
        // $consulta->bindParam(':nombre', $this->estado);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    /*
    public static function obtenerMesaPorID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesass WHERE id = $id");
        // $consulta->bindValue(1, $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $mesaBuscada = $consulta->fetchObject('Mesa');
        return $mesaBuscada;
    }
    */

    public static function obtenerTodasMesas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }
    
}