<?php

class Producto
{
    public $id;
    public $descripcion;
    public $precio;
    public $sector;

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (descripcion, precio, sector) VALUES (:descripcion, :precio, :sector)");

        $consulta->bindParam(':descripcion', $this->descripcion);
        $consulta->bindParam(':precio', $this->precio);
        $consulta->bindParam(':sector', $this->sector);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    /*
    public static function obtenerProductoPorID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id = $id");
        // $consulta->bindValue(1, $id, PDO::PARAM_INT);
        $consulta->execute();
        
        $productoBuscado = $consulta->fetchObject('Producto');
        return $productoBuscado;
    }
    */

    public static function obtenerTodosProductos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }
}
?>