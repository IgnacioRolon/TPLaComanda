<?php

namespace App\Controllers;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Mesa;
use Clases\Token;
use Clases\Utils;

class PedidoController{
    public function add($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "mozo")
        {
            try{
                $pedido = new Pedido;
                $params = (array)$request->getParsedBody(); 
                $pedido->codigoPedido = Utils::generateRandomString();
                while(Pedido::find($pedido->codigoPedido) != null) //Prevent repeated keys
                {
                    $pedido->codigoPedido = Utils::generateRandomString();
                }
                $pedido->estado = "en preparacion";
                $pedido->importe = $params['importe'];
                //Checkear que la mesa exista
                $mesa = Mesa::find($params['codigoMesa']);
                if($mesa != null)
                {
                    $pedido->codigoMesa = $params['codigoMesa'];
                    $mesa->estado = "con cliente esperando pedido";   

                    //Crear detalle del pedido con todos sus articulos                    
                    if(array_key_exists("bebida", $params) && $params['bebida'] != "")
                    {
                        $detallePedido = new DetallePedido;
                        $detallePedido->codigoPedido = $pedido->codigoPedido;
                        $detallePedido->articulo = $params["bebida"];
                        $detallePedido->sector = "tragos";
                        $detallePedido->tiempoEstimado = 0; //Se lo definirá quien lo prepare
                        $detallePedido->estado = "en espera";
                        
                        $detallePedido->save();
                    }
                    
                    if(array_key_exists("cerveza", $params) && $params['cerveza'] != "")
                    {
                        $detallePedido = new DetallePedido;
                        $detallePedido->codigoPedido = $pedido->codigoPedido;
                        $detallePedido->articulo = $params["cerveza"];
                        $detallePedido->sector = "cervezas";
                        $detallePedido->tiempoEstimado = 0; //Se lo definirá quien lo prepare
                        $detallePedido->estado = "en espera";
                        
                        $detallePedido->save();
                    }
                    
                    if(array_key_exists("comida", $params) && $params['comida'] != "")
                    {
                        $detallePedido = new DetallePedido;
                        $detallePedido->codigoPedido = $pedido->codigoPedido;
                        $detallePedido->articulo = $params["comida"];
                        $detallePedido->sector = "cocina";
                        $detallePedido->tiempoEstimado = 0; //Se lo definirá quien lo prepare
                        $detallePedido->estado = "en espera";
                        
                        $detallePedido->save();
                    }
                    
                    if(array_key_exists("postre", $params) && $params['postre'] != "")
                    {
                        $detallePedido = new DetallePedido;
                        $detallePedido->codigoPedido = $pedido->codigoPedido;
                        $detallePedido->articulo = $params["postre"];
                        $detallePedido->sector = "candy bar";
                        $detallePedido->tiempoEstimado = 0; //Se lo definirá quien lo prepare
                        $detallePedido->estado = "en espera";
                        
                        $detallePedido->save();
                    }
                    $mesa->save();
                    $pedido->save();  
                    $result = array("pedido" => $pedido->codigoPedido);                  
                }else{
                    $result = array("respuesta" => "La mesa indicada no existe.");
                    $response = $response->withStatus(400);
                }
            }catch(\Throwable $sh)
            {
                $result = array("respuesta" => "No se pudo crear el pedido.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para mozos.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function servirPedido($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "mozo")
        {
            $pedido = Pedido::find($args['id']);
            if($pedido != null)
            {
                $detalle = DetallePedido::where("estado", "!=", "listo para servir")
                                        ->where("codigoPedido", $pedido->codigoPedido)->first();
                //Si hay alguna entrada que no este lista, no se puede servir
                if($detalle == null)
                {
                    $pedido->estado = "servido";
                    $mesa = Mesa::find($pedido->codigoMesa);
                    $mesa->estado = "con clientes comiendo";
                    $pedido->save();
                    $mesa->save();

                    $result = array("respuesta" => "Pedido servido.");
                }else{
                    $result = array("respuesta" => "El pedido no está listo para servir.");
                    $response = $response->withStatus(400);
                }
            }else{
                $result = array("respuesta" => "El pedido indicado no existe.");
                $response = $response->withStatus(404);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para mozos.");
            $response = $response->withStatus(401);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function delete($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->tipo == "socio" || $decodedToken->tipo == "mozo")
        {
            $codigoPedido = $args['id'];
            $pedido = Pedido::find($codigoPedido);
            
            if($pedido != null)
            {            
                DetallePedido::where("codigoPedido", $pedido->codigoPedido)->delete();
                $pedido->delete();
                $result = array("respuesta" =>"Pedido cancelado exitosamente.");
            }else{
                $result = array("respuesta" =>"Error: Pedido no encontrado.");
                $response = $response->withStatus(400);
            }
        }else{
            $result = array("respuesta" => "Solo permitido para mozos y socios.");
            $response = $response->withStatus(401);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }
}