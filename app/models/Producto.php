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
    
    public static function ObtenerProductoPorID($id)
    {
        $productoBuscado = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion, precio, sector FROM productos WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->execute();
        
        $productoBuscado = $consulta->fetchObject('Producto');
        return $productoBuscado;
    }   

    public static function obtenerTodosProductos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function ModificarProducto($id, $descripcion, $precio, $sector)
    {
        // $retorno = false;
        try {
            //code...
            $objetoAccesoDato = AccesoDatos::obtenerInstancia(); 
            $consulta =$objetoAccesoDato->prepararConsulta("UPDATE productos SET descripcion = :descripcion, precio = :precio, sector = :sector WHERE id = :id");
            $consulta->bindParam(':descripcion', $descripcion);
            $consulta->bindParam(':precio', $precio);
            $consulta->bindParam(':sector', $sector);
            $consulta->bindParam(':id', $id);
            $consulta->execute();
            $retorno = true;
        } catch (\Throwable $th) {
            $retorno = false;
        }
        
        return $retorno;
    }
}
?>