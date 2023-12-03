<?php
require_once './models/Usuario.php';
// require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class UsuarioController extends Usuario /*implements IApiUsable*/
{
    
    public function CargarUsuario($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $puesto = $parametros['puesto'];
        $sector = $parametros['sector'];
        $mail = $parametros['mail'];
        $contrasena = $parametros['contrasena'];

        // Creamos el usuario
        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->puesto = $puesto;
        $usr->sector = $sector;
        $usr->mail = $mail;
        $usr->contrasena = $contrasena;
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito" . $usr->mail));

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

    public function CerrarMesaController(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];

        $mesa = Mesa::obtenerMesaPorID($idMesa);
        if($mesa->estado != 'cerrada'){
            if($mesa->estado == "abierta"){
                if(Usuario::CambiarEstadoMesa($idMesa,"cerrada")){
                    $retorno = json_encode(array("mensaje" => "mesa cerrada"));
                }else{
                    $retorno = json_encode(array("mensaje" => "mesa NO cerrada"));
                }
            }else{
                $retorno = json_encode(array("mensaje" => "mesa NO cerrada, tiene que tener el estado 'abierta' para cerrarse y esta mesa cuenta con clientes"));
            }
        }else{
            $retorno = json_encode(array("mensaje" => "la mesa ya esta cerrada"));
        }
        // $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken->puesto);

        $response->getBody()->write($retorno);
        return $response;
    }

    public function CambiarEstadoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];
        $estado = $parametros["estado"];

        // $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken->puesto);

        if(Usuario::CambiarEstadoMesa($idMesa, $estado)){
            $retorno = json_encode(array("mensaje" => "estado de la mesa cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado"));
        }
        $response->getBody()->write($retorno);
        return $response;
    }

    public function CambiarEstadoUsuarioController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idUsuario = $parametros["idUsuario"];
        $estado = $parametros["estado"];

        if(Usuario::CambiarEstadoUsuario($idUsuario, $estado)){
            $retorno = json_encode(array("mensaje" => "estado del usuario cambiado: " . $estado));
        }else{
            $retorno = json_encode(array("mensaje" => "estado no cambiado"));
        }
        $response->getBody()->write($retorno);
        return $response;


    }

    public function ModificarUsuarioController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idUsuario = $parametros["idUsuario"];
        $nombre = $parametros["nombre"];
        $puesto = $parametros["puesto"];
        $sector = $parametros["sector"];
        $mail = $parametros["mail"];
        $contrasena = $parametros["contrasena"];
        // $estado = $parametros["estado"];
        if(Usuario::ModificarUsuario($idUsuario,$nombre,$puesto,$sector,$mail,$contrasena)){
            $retorno = json_encode(array("mensaje" => "Usuario modificado: "));
        }else{
            $retorno = json_encode(array("mensaje" => "Usuario no modificado"));
        }
        $response->getBody()->write($retorno);
        return $response;

    }

    public function CargarUsuariosDesdeCsv($request,$response, $args)
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['archivo'] ?? null;

        if ($uploadedFile === null || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $retorno = json_encode(array("mensaje"=>"No se ha enviado ningun archivo o hubo un error en la carga"));
        }else{
            $tempFileName = $uploadedFile->getClientFilename();

            if (($archivo = fopen($tempFileName, "r")) !== false) {
                $encabezado = fgets($archivo);

                while (!feof($archivo)) {
                    $linea = fgets($archivo);
                    $datos = str_getcsv($linea);
                    
                    $usuario = new Usuario();
                    $usuario->id = $datos[0];
                    $usuario->nombre = (string)$datos[1];
                    $usuario->puesto = (string)$datos[2];
                    $usuario->sector = (string)$datos[3];
                    $usuario->ingresoSist = (string)$datos[4];
                    $usuario->cantOperaciones = (string)$datos[5];
                    $usuario->contrasena = (string)$datos[6];
                    $usuario->estado = (string)$datos[7];
                    $usuario->crearUsuario();
                }

                fclose($archivo);
                                
                $retorno = json_encode(array("mensaje"=>"Usuarios cargados en la bdd"));
            }else{
                $retorno = json_encode(array("mensaje"=>"Error en el archivo, no se encontro"));
            } 
        }

        $response->getBody()->write($retorno);
        return $response;
    }
    
    public function DescargarUsuariosDesdeCsv($request,$response, $args)
    {
        $path = "usuariosDesc.csv";
        $usuariosArray = Array();
        $usuarios = Usuario::obtenerTodosUsuarios();

        foreach ($usuarios as $usuarioInd) {
            $contrasena = Usuario::obtenerContrasenaPorID($usuarioInd->id);
            // var_dump($contrasena);
            $usuarioInd->contrasena = $contrasena;
        }        
        foreach($usuarios as $usuarioInd){
            $usuario = array($usuarioInd->id, $usuarioInd->nombre, $usuarioInd->puesto, $usuarioInd->sector, $usuarioInd->ingresoSist, $usuarioInd->cantOperaciones, $usuarioInd->contrasena, $usuarioInd->estado);
            $usuariosArray[] = $usuario;
        }

        $archivo = fopen($path, "w");
        $encabezado = array("id", "nombre", "puesto", "sector", "ingresoSist", "cantOperaciones", "contrasena", "estado");
        fputcsv($archivo, $encabezado);
        foreach($usuariosArray as $fila){
            fputcsv($archivo, $fila);
        }
        fclose($archivo);
        $retorno = json_encode(array("mensaje"=>"Usuarios guardados en CSV con exito"));
           
        $response->getBody()->write($retorno);
        return $response;
    }
}