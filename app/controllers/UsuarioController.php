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

        if(Usuario::ValidarPuesto($puesto)) {
            if(Usuario::ValidarSector($sector)) {
                if(Usuario::ValidarPuestoConSector($puesto,$sector)) {
                    // Creamos el usuario
                    $usr = new Usuario();
                    $usr->nombre = $nombre;
                    $usr->puesto = $puesto;
                    if($puesto == "mozo"){
                        $usr->sector = null;
                    }else{
                        $usr->sector = $sector;
                    }
                    $usr->crearUsuario();

                    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

                    $response->getBody()->write($payload);
                } else {
                    $payload = json_encode(array("error" => "La combinacion de puesto y sector no es valida, lo correcto es:<br>
                    <br>Puesto \t Sector
                    <br>bartender -> barra
                    <br>cerveceros -> choperas
                    <br>cocineros -> cocina o candy bar"));
                    
                    $response->getBody()->write($payload);
                }
            }else{
                $payload = json_encode(array("errror" => "Sector no valido. Los sectors disponibles son: barra, choperas, cocina, candy bar"));
            
                $response->getBody()->write($payload);
            }
        } else {
            $payload = json_encode(array("errror" => "Puesto no valido. Los puestos disponibles son: mozo, cocinero, bartender, socio, cervecero"));
            
            $response->getBody()->write($payload);
        }

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
    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodosUsuarios();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}