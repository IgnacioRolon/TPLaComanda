<?php

namespace App\Controllers;
use App\Models\Trabajador;
use Clases\Token;

class TrabajadorController{
    public function getAll($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio")
        {
            $rta = Trabajador::get();
        }else{
            $rta = array("respuesta" => "Solo permitido para socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function add($request, $response, $args) {
        $trabajador = new Trabajador;
        $params = (array)$request->getParsedBody();             
        try{
            $trabajador->nombre = $params['nombre'];
            $trabajador->contraseña = $params['contraseña'];
            $trabajador->tipo = $params['tipo'];
            $trabajador->estado = "disponible";

            //Check correct values
            if(strpos($trabajador->nombre, ' ') == false && strlen($trabajador->contraseña) > 3 &&
              ($trabajador->tipo == "bartender" || $trabajador->tipo == "cervecero" || $trabajador->tipo == "cocinero" || $trabajador->tipo == "mozo" || $trabajador->tipo == "socio"))
            {
                //All correct, save to DB
                $rta = $trabajador->save();
            }else{
                $result = array("respuesta" => "Datos inválidos. Reviselos e intentelo nuevamente.");
                $response = $response->withStatus(400);
            }            
            if($rta == true)
            {
                $result = array("respuesta" => "Trabajador registrado exitosamente."); 
            }else{
                $result = array("respuesta" => "No se pudo crear el trabajador.");
                $response = $response->withStatus(400);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" =>"Error: Datos inválidos o usuario ya registrado.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function login($request, $response, $args)
    {
        $params = (array)$request->getParsedBody();
        $trabajador = Trabajador::where('nombre', '=', $params['nombre'])->first();

        if($trabajador != null && $trabajador->contraseña == $params['contraseña'])
        {
            if($trabajador->estado == "suspendido")
            {
                $token = array("respuesta" => "El trabajador se encuentra suspendido.");
                $response = $response->withStatus(401);
            }else{
                $payload = array(
                    "id" => $trabajador->id,
                    "tipo" => $trabajador->tipo
                );
                $token = array("token" => Token::encode($payload));
            }
        }else{
            $token = array("respuesta" => "Usuario o contraseña invalidos.");
            $response = $response->withStatus(401);
        }

        $response->getBody()->write(json_encode($token));
        return $response;
    }

    public function updateEstado($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio")
        {
            $id = $args['id'];
            $trabajador = Trabajador::find($id);
            $params = (array)$request->getParsedBody();
            
            if($trabajador != null &&
               ($params['estado'] == "disponible" || $params['estado'] == "no disponible" || $params['estado'] == "suspendido"))
            {
                $trabajador->estado = $params['estado'];
                $rta = $trabajador->save();
                if($rta == true)
                {
                    $result = array("respuesta" => "Estado editado exitosamente.");
                }else{
                    $result = array("respuesta" => "No se pudo editar al trabajador.");
                    $response = $response->withStatus(400);
                }
            }else{
                $result = array("respuesta" =>"Error: Trabajador no encontrado o estado inválido.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function updateTipo($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio")
        {
            $id = $args['id'];
            $trabajador = Trabajador::find($id);
            $params = (array)$request->getParsedBody();
            
            if($trabajador != null &&
               ($params['tipo'] == "bartender" || $params['tipo'] == "cervecero" || $params['tipo'] == "cocinero" || $params['tipo'] == "mozo" || $params['tipo'] == "socio"))
            {
                $trabajador->tipo = $params['tipo'];
                $rta = $trabajador->save();
                if($rta == true)
                {
                    $result = array("respuesta" => "Tipo editado exitosamente.");
                }else{
                    $result = array("respuesta" => "No se pudo editar al trabajador.");
                    $response = $response->withStatus(400);
                }
            }else{
                $result = array("respuesta" =>"Error: Trabajador no encontrado o tipo inválido.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function delete($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio")
        {
            $id = $args['id'];
            $trabajador = Trabajador::find($id);
            
            if($trabajador != null)
            {            
                $rta = $trabajador->delete();
                if($rta == true)
                {
                    $result = array("respuesta" => "Trabajador eliminado exitosamente.");
                }else{
                    $result = array("respuesta" => "No se pudo eliminar al trabajador.");
                    $response = $response->withStatus(400);
                }
            }else{
                $result = array("respuesta" =>"Error: Trabajador no encontrado.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }
}