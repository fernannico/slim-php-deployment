<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
// require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa /*implements IApiUsable*/
{
    public function CargarMesa($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $estado = $parametros['estado'];

        $códigoIdentificación = rand(10000,99999);
        // Creamos el usuario
        $usr = new Mesa();
        $usr->id = $códigoIdentificación;
        $usr->estado = $estado;
        $usr->crearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /*
    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por id
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuarioPorID($id);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    */
    public function TraerTodas($request, $response, $args)
    {
        $lista = Mesa::obtenerTodasMesas();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MesaMasUsadaController($request, $response, $args)
    {
        $mesas = Mesa::ObtenerMesasMasUsadas();

        if (!$mesas) {
            $payload = json_encode(array("mensaje" => "No se encontró ninguna mesa"));
        } else {
            $payload = json_encode(['Mesas_Mas_Usadas' => $mesas]);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}