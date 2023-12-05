<?php
require_once './models/Usuario.php';
require_once './models/Mesa.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class UsuarioController extends Usuario 
{
    
    public function CargarUsuario($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['nombre']) && !empty($parametros['nombre']) && isset($parametros['puesto']) && !empty($parametros['puesto']) && isset($parametros['sector']) && !empty($parametros['sector']) && isset($parametros['mail']) && !empty($parametros['mail']) && isset($parametros['contrasena']) && !empty($parametros['contrasena'])){
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
    
            $payload = json_encode(array("mensaje" => "Usuario creado con exito " . $usr->mail));
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
            // $response->getBody()->write($payload);
        }

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

    public function ModificarUsuarioController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['idUsuario']) && !empty($parametros['idUsuario']) && isset($parametros['nombre']) && !empty($parametros['nombre']) && isset($parametros['puesto']) && !empty($parametros['puesto']) && isset($parametros['sector']) && !empty($parametros['sector']) && isset($parametros['mail']) && !empty($parametros['mail']) && isset($parametros['contrasena']) && !empty($parametros['contrasena'])){
            $idUsuario = $parametros["idUsuario"];
            $nombre = $parametros["nombre"];
            $puesto = $parametros["puesto"];
            $sector = $parametros["sector"];
            $mail = $parametros["mail"];
            $contrasena = $parametros["contrasena"];
            // $estado = $parametros["estado"];
            if(Usuario::ModificarUsuario($idUsuario,$nombre,$puesto,$sector,$mail,$contrasena)){
                $payload = json_encode(array("mensaje" => "Usuario modificado: "));
            }else{
                $payload = json_encode(array("mensaje" => "Usuario no modificado"));
            }
        }else{
            $payload = json_encode(array("error" => "faltan parametros"));   
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CerrarMesaController(Request $request, Response $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['idMesa']) && !empty($parametros['idMesa'])){
            $idMesa = $parametros["idMesa"];
            $mesa = Mesa::obtenerMesaPorID($idMesa);
            if($mesa->estado != 'cerrada'){
                if($mesa->estado == "abierta"){
                    if(Usuario::CambiarEstadoMesa($idMesa,"cerrada")){
                        $mesa->ActualizarImagenMesaPedido("sin imagen");
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
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
        }
        // $dataToken = $request->getAttribute('datosToken');
        // var_dump($dataToken->puesto);

        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstadoMesaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['idMesa']) && !empty($parametros['idMesa']) && isset($parametros['estado']) && !empty($parametros['estado'])){
            $idMesa = $parametros["idMesa"];
            $estado = $parametros["estado"];
    
            $continuar = true;
            $mesa = Mesa::obtenerMesaPorID($idMesa);
            if($mesa){
                if($estado === "con cliente pagando" && $mesa->estado !== "con cliente comiendo")
                {    
                    $continuar = false;
                    $retorno = json_encode(array("mensaje" => "estado no cambiado, La mesa no cuenta con los clientes comiendo"));
                }
        
                if($continuar){
                    if(Usuario::CambiarEstadoMesa($idMesa, $estado)){
                        $retorno = json_encode(array("mensaje" => "estado de la mesa cambiado: " . $estado));
                    }else{
                        $retorno = json_encode(array("mensaje" => "estado no cambiado"));
                    }
                }
            }else{
                $retorno = json_encode(array("error" => "no existe la mesa"));   
            }
        }else{
            $retorno = json_encode(array("error" => "faltan parametros"));   
        }
        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CobrarCuentaController($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigoAN = $parametros["codigoAN"];

        //que el pedido tenga estado entregado y modificarlo a cobrado
        //que la mesa tenga estado con cliente pagando
        $pedidos = Pedido::obtenerPedidosPorCodigoAN($codigoAN);
        $pedido = $pedidos[0];
        $mesa = Mesa::obtenerMesaPorID($pedido->idMesa);

        if($pedido->estado == "cobrado"){
            $retorno = json_encode(array("error" => "el pedido ya fue cobrado"));
        }else{
            if($mesa->estado == "con cliente pagando" && $pedido->estado == "entregado")
            {
                foreach($pedidos as $pedidoInd)
                {
                    Pedido::actualizarEstado($pedidoInd->idPedido,"cobrado");
                }
                Mesa::actualizarEstado($mesa->id,"abierta");
                $mesa->ActualizarImagenMesaPedido("sin imagen");
    
                $nombre_imagen = "pedido-".$pedido->codigoAN . "_mesa-" . $pedido->idMesa . ".jpg";       
                $carpetaOrigen = "ImagenesDePedidos/";
                $carpetaDestino = "ImagenesBackupPedidos/";        
    
                Pedido::MoverImagen($nombre_imagen,$carpetaOrigen,$carpetaDestino);
    
                $monto = Pedido::ObtenerMontoTotalPedido($codigoAN);
                $retorno = json_encode(array("mensaje" => "Pedidos con codigoAN " . $codigoAN . " cobrados. Monto total: $" .$monto));
            }else{
                $retorno = json_encode(array("error" => "el pedido tuvo que ser entregado y los clientes haber pedido la cuenta"));
            }
        }

        $response->getBody()->write($retorno);
        return $response->withHeader('Content-Type', 'application/json');
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
        return $response->withHeader('Content-Type', 'application/json');
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
        return $response->withHeader('Content-Type', 'application/json');
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
        return $response->withHeader('Content-Type', 'application/json');
    }
}