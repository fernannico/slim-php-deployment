<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './models/Usuario.php';
require_once './models/Mesa.php';

class AuthMesaMW
{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $idMesa = $parametros["idMesa"];
        $idUsuario = $parametros["idUsuario"];

        if ($this->existeUsuario($idUsuario)){
            if($this->existeMesa($idMesa)){ 
                return $handler->handle($request);
            }else{
                $response = new Response();
                $response->getBody()->write(json_encode(["mensaje" => "Mesa no encontrada"]));
            }
        } else {
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "Usuario no encontrado"]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function existeUsuario($idUsuario)
    {
        $retorno = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM usuarios WHERE id = :id");
        $consulta->bindParam(":id", $idUsuario);
        $consulta->execute();
    
        $id = $consulta->fetchColumn();
        if($id !== false){
            $retorno = true;
        } // Si el ID existe, fetchColumn devolverá el ID o false si no existe
        return $retorno;
    }
    
    private function existeMesa($idMesa)
    {
        $retorno = false;
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM mesas WHERE id = :id");
        $consulta->bindParam(":id", $idMesa);
        $consulta->execute();
    
        $id = $consulta->fetchColumn();

        if($id !== false){
            $retorno = true;
        } // Si el ID existe, fetchColumn devolverá el ID o false si no existe
        return $retorno;
    }
}