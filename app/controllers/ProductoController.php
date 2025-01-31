<?php
require_once './models/Producto.php';
require_once './models/Usuario.php';

class ProductoController extends Producto 
{
    public function CargarProducto($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['descripcion']) && !empty($parametros['descripcion']) && isset($parametros['precio']) && !empty($parametros['precio']) && isset($parametros['sector']) && !empty($parametros['sector'])){
            $descripcion = $parametros['descripcion'];
            $precio = $parametros['precio'];
            $sector = $parametros['sector'];
    
            if(Usuario::ValidarSector($sector)) {
                // Creamos el producto
                $usr = new Producto();
                $usr->descripcion = $descripcion;
                $usr->precio = $precio;
                $usr->sector = $sector;
                $usr->crearProducto();
                $payload = json_encode(array("mensaje" => "Producto creado con exito"));
                $response->getBody()->write($payload);
            } else {
                $payload = json_encode(array("errror" => "Sector no valido. Los sectors disponibles son: barra, choperas, cocina, candy bar"));
                
                $response->getBody()->write($payload);
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /*
    public function TraerUno($request, $response, $args)
    {
        // Buscamos producto por id
        $id = $args['id'];
        $producto = Producto::obtenerProductoPorID($id);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    */
    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodosProductos();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarProductoController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['idProducto']) && !empty($parametros['idProducto']) && isset($parametros['descripcion']) && !empty($parametros['descripcion']) && isset($parametros['precio']) && !empty($parametros['precio']) && isset($parametros['sector']) && !empty($parametros['sector'])){
            $id = $parametros["idProducto"];
            $descripcion = $parametros["descripcion"];
            $precio = $parametros["precio"];
            $sector = $parametros["sector"];
            if(Producto::ModificarProducto($id,$descripcion,$precio,$sector)){
                $productoModificado = Producto::ObtenerProductoPorID($id);
                $retorno = json_encode(array("Producto_modificado" => $productoModificado));
            }else{
                $retorno = json_encode(array("mensaje" => "Producto no modificado"));
            }
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
            // $response->getBody()->write($payload);
        }
        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');        
    }
}