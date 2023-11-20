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
    
    public static function obtenerMesaPorID($id)
    {
        $mesaBuscada = null;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado FROM mesas WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $mesaBuscada = $consulta->fetchObject('Mesa');
        return $mesaBuscada;
    }

    public static function obtenerTodasMesas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }
    
    public static function actualizarEstado($id, $estado)
    {
        $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");
            $consulta->bindParam(":id", $id);
            $consulta->bindParam(":estado", $estado);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }

        return $retorno;
    }

    public static function ObtenerEstadoPorID($id)
    {
        $estado = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT estado FROM mesas WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $estado = $consulta->fetchColumn();
        return $estado;
    }    
}