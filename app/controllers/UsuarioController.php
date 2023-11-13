<?php
require_once './models/Usuario.php';
// require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario /*implements IApiUsable*/
{
    public function CargarUsuario($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $puesto = $parametros['puesto'];
        $sector = $parametros['sector'];

        // Creamos el usuario
        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->puesto = $puesto;
        // if($puesto == "mozo"){
        //     $usr->sector = null;
        // }else{
        //     $usr->sector = $sector;
        // }
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function TraerUno($request, $response, $args)
    {
        //ver que pasa con requestQuery que siempre trae todos
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuarioPorID($id);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodosUsuarios();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CerrarMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];
        $idUsuario = $parametros["idUsuario"];

        if(Usuario::CerrarMesa($idUsuario,$idMesa)){
            $retorno = json_encode(array("mensaje" => "mesa cerrada"));
        }else{
            $retorno = json_encode(array("mensaje" => "mesa NO cerrada, usuario no es socio"));
        }
        $response->getBody()->write($retorno);
        return $response;

        // HACER: MW que valide que exista el usuario y el id de la mesa para que entre al controller
    }

}