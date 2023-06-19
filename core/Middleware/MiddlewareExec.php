<?php

namespace Myframework\Middleware;

use Myframework\Middleware\MiddlewareStack;

final class MiddlewareExec
{

    protected array $middlewares;
    protected int $current_middleware = 0;

    public function __construct(array $middlewares)
    {
        // [
        //     'guest' => 'ourmiddleware',
        //      Guest::class
        // ];
        // if (empty($middlewares) || !is_array($middlewares)) {
        //     throw new \Exception("Middleware key Must be Empty");
        // }
        $this->middlewares = $middlewares;
    }

    public function resolve($request)
    {
        $this->next($request);
    }

    function next($request): void
    {
        if ($this->current_middleware > (count($this->middlewares) - 1)) {
            return;
        }

        $current_middleware = $this->middlewares[$this->current_middleware];

       

        // $middleware = new $current_middleware;
        $middleware = MiddlewareStack::get($current_middleware);
        if (!$middleware instanceof MiddlewareInterface) {
            throw new \Exception($current_middleware . " does not implement Myframework/Http/Middelware/MiddlewareInterface");
        }

        $this->current_middleware++;
        $middleware->handle($request, function ($request) {
            return $this->next($request);
        });
    }
}
