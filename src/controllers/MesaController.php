<?php

namespace App\Controllers;
use Clases\Utils;
use Clases\Token;
use App\Models\Mesa;

class MesaController{
    public function add($request, $response, $args) {
        $mesa = new Mesa;           
        try{
            $mesa->codigoMesa = Utils::generateRandomString();
            while(Mesa::find($mesa->codigoMesa) != null) //Prevent repeated keys
            {
                $mesa->codigoMesa = Utils::generateRandomString();
            }
            $mesa->estado = "cerrada";
                //All correct, save to DB
            $rta = $mesa->save();    
            if($rta == true)
            {
                $result = array("mesa" => $mesa->codigoMesa);                
            }else{
                $result = array("respuesta" => "No se pudo crear la mesa.");
                $response = $response->withStatus(400);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" =>"No se pudo crear la mesa.");
            $response = $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function updateEstado($request, $response, $args) {        
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio" || $decodedToken->tipo == "mozo")
        {
            $id = $args['id'];
            $mesa = Mesa::find($id);
            $params = (array)$request->getParsedBody();
            if($mesa != null && 
            ($params['estado'] == "con cliente esperando pedido" || $params['estado'] == "con clientes comiendo" || $params['estado'] == "con clientes pagando" || $params['estado'] == "cerrada") )
            {                
                if($params['estado'] == "cerrada" && $decodedToken->tipo != "socio")
                {
                    $result = array("respuesta" => "Solo permitido para socios.");
                    $response = $response->withStatus(401);
                }else{
                    $mesa->estado = $params['estado'];
                    $rta = $mesa->save();
                    if($rta == true)
                    {
                        $result = array("respuesta" => "Estado editado exitosamente.");
                    }else{
                        $result = array("respuesta" => "No se pudo editar la mesa.");
                        $response = $response->withStatus(400);
                    }
                }
            }else{
                $result = array("respuesta" =>"Error: Mesa no encontrada o estado inválido.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para mozos y socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function getAll($request, $response, $args) {
        $rta = Mesa::get();
        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
}