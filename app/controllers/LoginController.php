<?php
require_once './models/Usuario.php';
//require_once './middlewares/autenticadorMW.php';
require_once './JWT/AuthJWT.php';
class LoginController{

    public function LoginController($request, $response, $args){
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $contrasenia = $parametros['contrasena'];
        $usuario = null;
        $usuario = Usuario::ObtenerUsuarioPorNamePwd($nombre, $contrasenia);

        if($usuario !== null){ 
            $datos = array('id' => $usuario->id, 'sector'=> $usuario->sector);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error: ' => 'Usuario / contraseña no coinciden'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>