<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';

class Usuario
{
    public $id;
    public $nombre;
    public $puesto;
    public $sector;
    public $ingresoSist;
    public $cantOperaciones;
    public $contrasena;
    public $estado;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, puesto, sector,contrasena) VALUES (:nombre, :puesto, :sector, :contrasena)");

        $consulta->bindParam(':nombre', $this->nombre);
        $consulta->bindParam(':puesto', $this->puesto);
        $consulta->bindParam(':sector', $this->sector);
        $consulta->bindParam(':contrasena', $this->contrasena);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    // public static function ValidarPuesto($puesto){
    //     // validar puesto
    //     $retorno = false;
    //     $puestoesPermitidos = ["mozo", "cocinero", "bartender", "socio", "cervecero"];
    //     if (in_array($puesto, $puestoesPermitidos)) {
    //         $retorno = true;
    //     }

    //     return $retorno;
    // }
    
    public static function ValidarSector($sector){
        // validar sector
        $retorno = false;
        $sectoresPermitidos = ["barra", "choperas", "cocina", "candy bar","mozos","socios"];
        if (in_array($sector, $sectoresPermitidos)) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function obtenerUsuarioPorID($id)
    {
        $usuarioBuscado = null;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, puesto, sector, ingresoSist, cantOperaciones, estado FROM usuarios WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $usuarioBuscado = $consulta->fetchObject('Usuario');
        return $usuarioBuscado;
    }    

    public static function ObtenerUsuarioPorNamePwd($nombre, $password){
        $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT id, nombre, contrasena, sector from usuarios where nombre = :nombre AND contrasena = :contrasena AND estado = 'activo'");
        $consulta->bindParam(":nombre", $nombre);
        $consulta->bindParam(":contrasena", $password);
        $consulta->execute();
        $usuario = $consulta->fetchObject();
        return $usuario;
    }
    public static function obtenerTodosUsuarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, puesto, sector, ingresoSist, cantOperaciones, estado FROM usuarios");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
    
    public static function CerrarMesa($idUsuario, $idMesa)
    {
        $retorno = false;
        $usuario = self::obtenerUsuarioPorID($idUsuario);
        // $mesa = Mesa::obtenerMesaPorID($idMesa);
        if($usuario->puesto == "socio"){
            Mesa::actualizarEstado($idMesa, "cerrada");
            $retorno = true;
        }
        return $retorno;
    }
        
    public static function CambiarEstadoMesa($idUsuario, $idMesa, $estado)
    {
        $retorno = false;
        $usuario = self::obtenerUsuarioPorID($idUsuario);
        // $mesa = Mesa::obtenerMesaPorID($idMesa);
        if($usuario->puesto == "mozo"){
            Mesa::actualizarEstado($idMesa, $estado);
            $retorno = true;
        }
        return $retorno;
    }
}   