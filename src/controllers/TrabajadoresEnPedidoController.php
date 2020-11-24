<?php

namespace App\Controllers;
use App\Models\TrabajadoresEnPedido;
use App\Models\DetallePedido;
use Clases\Token;

class TrabajadoresEnPedidoController{
    public function add($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        $trabajador = new TrabajadoresEnPedido;
        $idDetalle = $args['id'];
        try{
            $trabajador->idTrabajador = $decodedToken->id;
            $params = (array)$request->getParsedBody(); 
            $detalle = DetallePedido::find($idDetalle);
            if($detalle != null){
                $trabajador->idDetalle = $idDetalle;
                $detalle->tiempoEstimado = $params['tiempoEstimado'];
                $detalle->estado = "en preparacion";
                switch($decodedToken->tipo)
                {
                    case "bartender":
                        $sectores = array("tragos");
                    break;
                    case "cervecero":
                        $sectores = array("cervezas");
                    break;
                    case "cocinero":
                        $sectores = array("cocina", "candy bar");                        
                    break;
                    default:
                        $sectores = array("");
                }
                if(in_array($detalle->sector, $sectores)) //El pedido es del sector correspondiente
                {
                    $detalle->save();
                    $rta = $trabajador->save();           
                    if($rta == true)
                    {
                        $result = array("respuesta" => "Pedido asignado correctamente.");
                        $response = $response->withStatus(400);
                    }else{
                        $result = array("respuesta" => "No se pudo asignar el pedido.");
                        $response = $response->withStatus(400);
                    }                    
                }else{
                    $result = array("respuesta" => "El pedido no pertenece a tu sector.");
                    $response = $response->withStatus(401);
                }
            }else{
                $result = array("respuesta" => "El pedido indicado no existe."); 
                $response = $response->withStatus(404);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" =>"Error: Datos inválidos. No se pudo asignar el pedido.");
            $response = $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}