<?php

namespace App\Controllers;
use App\Models\DetallePedido;
use App\Models\TrabajadoresEnPedido;
use App\Models\Pedido;
use Clases\Token;
use DateTime;

class DetallePedidoController{
    public function getAll($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        $sector = "";
        $sector2 = "";
        switch($decodedToken->tipo)
        {
            case "bartender":
                $sector = "tragos";
            break;
            case "cervecero":
                $sector = "cervezas";
            break;
            case "cocinero":
                $sector = "cocina";
                $sector2 = "candy bar";
            break;
            default:
                $sector = "";
        }
        if($decodedToken->tipo == "cocinero")
        {
            $rta = DetallePedido::whereIn("sector", array($sector, $sector2))
                                ->whereIn("estado", array("en espera", "en preparacion"))->get();
        }else if($decodedToken->tipo == "socio"){
            $rta = DetallePedido::get();
        }else{
            $rta = DetallePedido::where("sector", $sector)
                                ->whereIn("estado", array("en espera", "en preparacion"))->get();
        }
        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function updateEstado($request, $response, $args) {        
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        try{
            $detalle = DetallePedido::find($args['id']);
            if($detalle != null)
            {
                $trabajadorDesignado = TrabajadoresEnPedido::where("idDetalle", $detalle->id)
                                                            ->where("idTrabajador", $decodedToken->id)->first();
                if($trabajadorDesignado != null)
                {
                    $detalle->estado = "listo para servir";
                    $detalle->tiempoEstimado = 0;
    
                    $detalle->save();
                    $result = array("respuesta" => "Articulo listo para servir.");                    
                }else{
                    $result = array("respuesta" => "El detalle indicado no está asignado a tu nombre.");
                    $response = $response->withStatus(401);
                }
            }else{
                $result = array("respuesta" => "No se encontro el detalle indicado.");
                $response = $response->withStatus(400);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" => "No se pudo editar el detalle indicado.");
            $response = $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }    

    public function getTiempo($request, $response, $args) {
        try{
            //Obtenemos el detalle con el tiempo estimado mas alto
            //Primero obtenemos cuanto tiempo paso desde que se solicito el pedido hasta ahora
            $params = $request->getQueryParams();
            $codigoPedido = $params['codigoPedido'];
            $codigoMesa = $params['codigoMesa'];
            
            $detalle = DetallePedido::where("codigoPedido", $codigoPedido)
                                    ->orderBy('tiempoEstimado', 'desc')->first();
            $pedido = Pedido::where("codigoPedido", $detalle->codigoPedido)
                            ->where("codigoMesa", $codigoMesa)->first();
            if($detalle != null && $pedido != null){
                $horaPedido = new DateTime($pedido->created_at);
                $horaActual = new DateTime();

                $tiempoDesdePedido = $horaPedido->diff($horaActual);
                $minutes = $tiempoDesdePedido->days * 24 * 60;
                $minutes += $tiempoDesdePedido->h * 60;
                $minutes += $tiempoDesdePedido->i;
                $minutes = $detalle->tiempoEstimado - $minutes;

                if($minutes > 0)
                {
                    $result = array("respuesta" => "Tiempo estimado restante: {$minutes} minutos");
                }else{
                    $result = array("respuesta" => "Su pedido estará listo en breve");
                }
            }else{
                $result = array("respuesta" => "No se encontro el pedido solicitado.");
                $response = $response->withStatus(404);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" => "No se pudo obtener el tiempo: {$sh->getMessage()}");
            $response = $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}