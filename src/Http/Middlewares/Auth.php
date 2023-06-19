<?php

namespace App\Http\Middlewares;

use Closure;
use Myframework\Http\Token;
use Myframework\Middleware\MiddlewareInterface;
use Myframework\Http\Request;
use Myframework\Http\Response;

class Auth implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {

        $response = new Response();
        if (!isLogin()) {
            $response->code(401);
            $data['error'] = 'Session expired. Please re-signin.';
            $response->withJson($data, 0);
        }
        return $next($request);
    }

}