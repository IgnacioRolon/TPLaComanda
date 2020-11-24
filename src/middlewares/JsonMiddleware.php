<?php

namespace App\Middlewares;

class JsonMiddleware{
    public function __invoke($request, $handler)
    {
        $response = $handler->handle($request);
        $response = $response->withHeader('Content-type', 'application/json');

        return $response;
    }
}