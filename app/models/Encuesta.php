<?php
class Encuesta{
    public $id;
    public $idMesa;
    public $codigoAN;
    public $nombreCliente;
    public $puntuacionMesa;
    public $puntuacionRestaurante;
    public $puntuacionMozo;
    public $puntuacionCocinero;
    public $promedioPuntaje;
    public $comentario;

    public static function CrearEncuesta($encuesta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuestas (idMesa,codigoAN,nombreCliente,puntuacionMesa,puntuacionRestaurante,puntuacionMozo,puntuacionCocinero,promedioPuntaje,comentario) VALUES (:idMesa,:codigoAN,:nombreCliente,:puntuacionMesa,:puntuacionRestaurante,:puntuacionMozo,:puntuacionCocinero,:promedioPuntaje,:comentario)");
        $consulta->bindValue(':idMesa', $encuesta->idMesa);
        $consulta->bindValue(':codigoAN', $encuesta->codigoAN);
        $consulta->bindValue(':nombreCliente', $encuesta->nombreCliente);
        $consulta->bindValue(':puntuacionMesa', $encuesta->puntuacionMesa);
        $consulta->bindValue(':puntuacionRestaurante', $encuesta->puntuacionRestaurante);
        $consulta->bindValue(':puntuacionMozo', $encuesta->puntuacionMozo);
        $consulta->bindValue(':puntuacionCocinero', $encuesta->puntuacionCocinero);
        $consulta->bindValue(':promedioPuntaje', $encuesta->promedioPuntaje);
        $consulta->bindValue(':comentario', $encuesta->comentario);
        $consulta->execute();
    }

    public static function ObtenerTodasEncuestas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function ObtenerEncuestaPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas WHERE id = :id");
        $consulta->bindParam(':id', $id);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }
    public static function ObtenerMejoresComentarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas ORDER BY promedioPuntaje DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
}