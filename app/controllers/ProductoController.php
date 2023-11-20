<?php
require_once './models/Producto.php';
require_once './models/Usuario.php';
// require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto /*implements IApiUsable*/
{
    public function CargarProducto($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

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
        $id = $parametros["id"];
        $descripcion = $parametros["descripcion"];
        $precio = $parametros["precio"];
        $sector = $parametros["sector"];
        if(Producto::ModificarProducto($id,$descripcion,$precio,$sector)){
            $retorno = json_encode(array("mensaje" => "Producto modificado: "));
        }else{
            $retorno = json_encode(array("mensaje" => "Producto no modificado"));
        }
        $response->getBody()->write($retorno);
        return $response;        
    }
}