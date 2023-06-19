<?php

namespace App\Http\Middlewares;

use Closure;
use Myframework\Http\Response;
use Myframework\Middleware\MiddlewareInterface;
use Myframework\Http\Request;

class Guest implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if (isLogin()) {
            $response = new Response();
            $data['message'] = 'You have aleady signin';
            $response->withJson($data, 0);
        }
        return $next($request);
    }
} 