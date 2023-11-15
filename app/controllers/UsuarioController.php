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
        $contrasena = $parametros['contrasena'];

        // Creamos el usuario
        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->puesto = $puesto;
        $usr->sector = $sector;
        $usr->contrasena = $contrasena;
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    
    public function TraerUno($request, $response, $args)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        
        if(isset($id) & $id !== ""){
            $usuario = Usuario::obtenerUsuarioPorID($id);

            if ($usuario) {
                $payload = json_encode($usuario);
                $response->getBody()->write($payload);
            } else {
                $response->getBody()->write(json_encode(["error" => "Usuario no encontrado"]));
            }
        }else{
            $response->getBody()->write(json_encode(["error" => "ID de usuario no proporcionado"]));            
        }
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

    public function CambiarEstadoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];
        $idUsuario = $parametros["idUsuario"];
        $estado = $parametros["estado"];

        if(Usuario::CambiarEstadoMesa($idUsuario,$idMesa, $estado)){
            $retorno = json_encode(array("mensaje" => "estado de la mesa cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado, usuario no es mozo"));
        }
        $response->getBody()->write($retorno);
        return $response;

    }
}