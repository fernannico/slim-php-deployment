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
    public $mail;
    public $contrasena;
    public $estado;

    // public function __construct($id = "",$nombre = "",$puesto = "",$sector = "",$ingresoSist = "",$cantOperaciones = "",$contrasena = "",$estado = "") {
    //     $this->id = $id;
    //     $this->nombre = $nombre;
    //     $this->puesto = $puesto;
    //     $this->sector = $sector;
    //     $this->ingresoSist = $ingresoSist;
    //     $this->cantOperaciones = $cantOperaciones;
    //     $this->contrasena = $contrasena;
    //     $this->estado = $estado;
    // }
    public function CrearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, puesto, sector, mail, contrasena) VALUES (:nombre, :puesto, :sector, :mail, :contrasena)");

        $consulta->bindParam(':nombre', $this->nombre);
        $consulta->bindParam(':puesto', $this->puesto);
        $consulta->bindParam(':sector', $this->sector);
        $consulta->bindParam(':mail', $this->mail);
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

    public static function ObtenerUsuarioPorID($id)
    {
        $usuarioBuscado = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, puesto, sector, ingresoSist, cantOperaciones, estado FROM usuarios WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $usuarioBuscado = $consulta->fetchObject('Usuario');
        return $usuarioBuscado;
    }    

    public static function ObtenerUsuarioPorMailPwd($mail, $password) {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT id, nombre, contrasena, sector, puesto from usuarios where mail = :mail AND contrasena = :contrasena");
        $consulta->bindParam(":mail", $mail);
        $consulta->bindParam(":contrasena", $password);
        $consulta->execute();
    
        $usuario = $consulta->fetchObject();
        if ($usuario === false) {
            return false;
        }
        return $usuario;
    }
    public static function obtenerTodosUsuarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, puesto, sector, ingresoSist, cantOperaciones, estado FROM usuarios");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
    public static function ObtenerContrasenaPorID($id)
    {
        $contrasena = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT contrasena FROM usuarios WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        // $usuarioBuscado = $consulta->fetchObject('Usuario');
        // $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        $contrasena = $consulta->fetchColumn();
        return $contrasena;
    }    
        
    public static function CambiarEstadoMesa($idMesa, $estado)
    {
        $retorno = false;
        if(Mesa::actualizarEstado($idMesa, $estado)){
            $retorno = true;
        }
        return $retorno;
    }

    public static function CambiarEstadoUsuario($idUsuario, $estado) 
    {
        $retorno = false;
        if(Usuario::actualizarEstado($idUsuario, $estado)){
            $retorno = true;
        }
        return $retorno;        
    }

    public static function ActualizarEstado($idUsuario, $estado)
    {
        $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE usuarios SET estado = :estado WHERE id = :id");
            $consulta->bindParam(":id", $idUsuario);
            $consulta->bindParam(":estado", $estado);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }

        return $retorno;
    }

    public static function ModificarUsuario($id, $nombre, $puesto, $sector, $mail, $contrasena)
    {
        // $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
            $consulta =$objetoAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, puesto = :puesto, sector = :sector, mail = :mail, contrasena = :contrasena WHERE id = :id");
            $consulta->bindParam(':nombre', $nombre);
            $consulta->bindParam(':puesto', $puesto);
            $consulta->bindParam(':sector', $sector);
            $consulta->bindParam(':mail', $mail);
            $consulta->bindParam(':contrasena', $contrasena);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }
        
        return $retorno;
    }
}   
