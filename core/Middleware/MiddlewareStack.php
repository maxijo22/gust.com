<?php

namespace Myframework\Middleware;


class MiddlewareStack
{
    protected static $middlewareStack;

    static function has($key)
    {
        $middleware = static::$middlewareStack[$key] ?? false;
        if (!$middleware) return false;
        return true;
    }

    static function get($middleware)
    {

        if (!static::has($middleware)) {
            throw new \Exception(" no middleware key found for  " . $middleware);
        };

        $middleware =  static::$middlewareStack[$middleware];
        if (!class_exists($middleware)) {
            throw new \Exception($middleware . " this class does not exit or no middleware key found");
        };
        return new $middleware;
    }

    static function set(array $middlewares)
    {
        static::$middlewareStack = $middlewares;
    }
}
