<?php
require_once './models/Usuario.php';
//require_once './middlewares/autenticadorMW.php';
require_once './JWT/AuthJWT.php';
class LoginController{

    public function LoginController($request, $response, $args){
        $parametros = $request->getParsedBody();

        $mail = $parametros['mail'];
        $contrasenia = $parametros['contrasena'];
        $usuario = null;
        $usuario = Usuario::ObtenerUsuarioPorMailPwd($mail, $contrasenia);

        if($usuario){ 
            $datos = array('id' => $usuario->id, 'sector'=> $usuario->sector, 'puesto'=> $usuario->puesto);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error: ' => 'Usuario / contrasena no coinciden'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>