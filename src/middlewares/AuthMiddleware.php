<?php

namespace App\Middlewares;
use Clases\Token;
use Slim\Psr7\Response;

class AuthMiddleware{
    public function __invoke($request, $handler)
    {
        if(!Token::verify())
        {
            $response = new Response();
            $rta = array("error" => "Invalid Token");
            $response = $response->withStatus(401);

            $response->getBody()->write(json_encode($rta));
            return $response;
        }else{
            $response = $handler->handle($request);
            return $response;
        }
    }
}