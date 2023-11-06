<?php

class Usuario
{
    public $id;
    public $nombre;
    public $puesto;
    public $sector;
    public $ingresoSist;
    public $cantOperaciones;
    public $estado;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, puesto, sector) VALUES (:nombre, :puesto, :sector)");

        $consulta->bindParam(':nombre', $this->nombre);
        $consulta->bindParam(':puesto', $this->puesto);
        $consulta->bindParam(':sector', $this->sector);
        // $consulta->bindParam(':nombre', $this->estado);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ValidarPuesto($puesto){
        // validar puesto
        $retorno = false;
        $puestoesPermitidos = ["mozo", "cocinero", "bartender", "socio", "cervecero"];
        if (in_array($puesto, $puestoesPermitidos)) {
            $retorno = true;
        }

        return $retorno;
    }
    
    public static function ValidarSector($sector){
        // validar sector
        $retorno = false;
        $sectoresPermitidos = ["barra", "choperas", "cocina", "candy bar"];
        if (in_array($sector, $sectoresPermitidos)) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function ValidarPuestoConSector($puesto,$sector){
        // Validar restricciones adicionales según el puesto y el sector
        $retorno = false;
        if (($puesto == "cervecero" && $sector == "choperas") || ($puesto == "bartender" && $sector == "barra") || ($puesto == "cocinero" && in_array($sector, ["cocina", "candy bar"]))) {
            $retorno = true;
        }

        return $retorno;
    }

    /*
    public static function obtenerUsuarioPorID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE id = $id");
        // $consulta->bindValue(1, $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $usuarioBuscado = $consulta->fetchObject('Usuario');
        return $usuarioBuscado;
    }
    */

    public static function obtenerTodosUsuarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
    
}