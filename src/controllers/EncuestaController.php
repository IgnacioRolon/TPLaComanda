<?php

namespace App\Controllers;
use App\Models\Encuesta;

class EncuestaController{
    public function add($request, $response, $args) {
        $encuesta = new Encuesta;       
        $params = (array)$request->getParsedBody();    
        try{
            $puntajeMesa = $params['puntajeMesa'];
            $puntajeRestaurant = $params['puntajeRestaurant'];
            $puntajeMozo = $params['puntajeMozo'];
            $puntajeCocinero = $params['puntajeCocinero'];            
            if($puntajeMesa > 0 && $puntajeMesa < 11 && $puntajeRestaurant > 0 && $puntajeRestaurant < 11 &&
               $puntajeMozo > 0 && $puntajeMesa < 11 && $puntajeCocinero > 0 && $puntajeRestaurant < 11)
            {
                $encuesta->puntajeMesa = $puntajeMesa;
                $encuesta->puntajeRestaurant = $puntajeRestaurant;
                $encuesta->puntajeMozo = $puntajeMozo;
                $encuesta->puntajeCocinero = $puntajeCocinero;
                
                $rta = $encuesta->save();    
                if($rta == true)
                {
                    $result = array("respuesta" => "Encuesta recibida. Muchas gracias por visitarnos!");                
                }else{
                    $result = array("respuesta" => "No se pudo guardar la encuesta.");
                    $response = $response->withStatus(400);
                }
            }else{
                $result = array("respuesta" => "Los puntajes deben ser del 1 al 10.");
                $response = $response->withStatus(400);
            }                
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" => "No se pudo guardar la encuesta. {$sh->getMessage()}");
            $response = $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}