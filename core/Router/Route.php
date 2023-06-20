<?php

namespace Myframework\Router;

use Myframework\Http\Response;
use Myframework\Middleware\MiddlewareExec;

use Myframework\Http\Request;
use Closure;

class RouteBuilder
{
    private string $method;
    private string $url;
    private mixed $handler;
    private $pattern = null;
    private array $middleware = [];
    protected static array $group_middleware = [];
    public static string $prefix = '';

    public function __construct($method, $url, $handler)
    {

        $this->method = $method;
        $this->url = $url;
        $this->handler = $handler;
    }

    public function middleware($middleware)
    {
        if (empty($middleware)) {
            throw new \Exception("you must provide a middleware key");
        }
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
    public function pattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    private function validateUrl($url)
    {
        if ($url == '') {
            throw new \Exception("trying to define a route with an empty string");
        }
    }

    public function __destruct()
    {
        $prefix = static::$prefix;
        $url = $this->url;

        $this->validateUrl($url);


        if (!str_starts_with($url, '/')) {
            $url = "/$url";
        }
        $url = $prefix . $url;

        $url = $url . "=" . $this->method;
        if ($url != '/') {
            $url = rtrim($url, '/');
        }


        $build = [
            $url => [
                // 'method' => ,
                'handler' => $this->handler,
                'middleware' => array_merge(static::$group_middleware, $this->middleware),
                'pattern' => $this->pattern,
            ]
        ];
        if (array_key_exists($url, Route::$routes)) {
            $url = explode('=', $url);
            $method = $url[1];
            $url = $url[0];
            throw new \Exception("Trying to define a route that has already been declared url: {$url} with method {$method}");
            // Route::$routes
        }
        Route::$routes = array_merge(Route::$routes, $build);
    }
}

class GroupBuilder extends RouteBuilder
{
    private ?closure $callback = null;
    public function __construct(string $prefix, Closure $callback)
    {
        static::$prefix = $prefix;
        $this->callback = $callback;
    }

    public function middleware($middleware)
    {
        if (empty($middleware)) {
            throw new \Exception("you must provide a middleware key");
        }
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        static::$group_middleware = array_merge(static::$group_middleware, $middleware);
        return $this;
    }
    public function __destruct()
    {
        if (!is_null($this->callback)) {
            call_user_func($this->callback);
            static::$prefix = '';
            static::$group_middleware = [];
            // echo RouteBuilder::$prefix;
        }
        ;
    }
}

class Route
{
    public static string $controller_path = 'App\Http\Controllers';
    // public static string $base_middleware_path = 'Myframework\Middleware\\BaseMiddleware';
    private static $container;

    public static $routes = [];

    public static function setContainer($container)
    {
        self::$container = $container;
    }
    public static function addRoute($method, $url, $handler)
    {
        $builder = new RouteBuilder($method, $url, $handler);
        return $builder;
    }
    public static function get($url, $handler)
    {
        return static::addRoute('GET', $url, $handler);
    }
    public static function post($url, $handler)
    {
        return static::addRoute('POST', $url, $handler);
    }
    public static function patch($url, $handler)
    {
        return static::addRoute('PATCH', $url, $handler);
    }
    public static function delete($url, $handler)
    {
        return static::addRoute('DELETE', $url, $handler);
    }
    public static function options($url, $handler)
    {
        return static::addRoute('options', $url, $handler);
    }
    public static function put($url, $handler)
    {
        return static::addRoute('PUT', $url, $handler);
    }
    public static function group(string $prefix, Closure $callback)
    {

        $group = new GroupBuilder($prefix, $callback);
        return $group;
    }

    public static function dispatch($request)
    {


        $url = $request->path();
        $method = $request->method();

        foreach (static::$routes as $key => $route) {

            $key = explode('=', $key);
            $routes_method = $key[1];
            $key = $key[0];

            if (str_ends_with($key, '/')) {
                $key = rtrim($key, '/');
            }

            if (!str_starts_with($key, '/')) {
                $key = "/$key";
            }


            $pattern = '#:(\w+)#';

            // Extract all named capture group names from the pattern
            preg_match_all($pattern, $key, $group);
            // Extract only the named capture groups from the $matches array
            $group_params = $group[1];
            // Construct the regular expression pattern with named capture groups
            $pattern = preg_replace('#:(\w+)#', '(?P<$1>[^/]+)', $key);

            // check if route method and request_url pattern match the current request method and request_url

            if ($routes_method === $method && preg_match("#^($pattern)$#", $url, $matches)) {
                // map and call middle ware
                $params = array_intersect_key($matches, array_flip($group_params));
                // $middleware = new static::$base_middleware_path;
                if ($method != 'GET') {
                    /** 
                     * TODO  implement automative verification for crsf
                     */
                    // $middleware->resolve(['VerifyCsrfToken']);
                }

                if (!empty($route['middleware'])) {
                    $middleware = new MiddlewareExec($route['middleware']);
                    $middleware->resolve(Request::createFromGlobal());
                }

                static::handle_request($route['handler'], $params);

                return;
            }
        }


        // dd(Route::$routes);

        static::abort();
    }

    private static function handle_request($handler, $args = [])
    {

        if (is_string($handler)) {
            self::string_handler($handler, $args);
        }
        if (is_callable($handler)) {
            $callback = static::$container->call($handler, $args);
            self::callback_return($callback);
        }
        if (is_array($handler)) {
            self::array_handler($handler, $args);
        }
    }
    protected static function string_handler($handler, $args)
    {
        $segments = explode('@', $handler);
        $controller = static::$controller_path . "\\$segments[0]";
        if (class_exists($segments[0])) {
            $controller = $segments[0];
        }
        $method = $segments[1] ?? 'index';

        self::invoke_handler($controller, $method, $args);
    }
    protected static function array_handler($handler, $args)
    {
        $controller = $handler[0];
        $method = $handler[1] ?? 'index';
        self::invoke_handler($controller, $method, $args);
    }

    private static function invoke_handler($controller, $method, $args = [])
    {
        // call the functions using the container
        $callback = static::$container->call([$controller, $method], $args);
        self::callback_return($callback);

    }

    /** 
     *
      DISPLAY WHAT THE CALLBACK RETURN 
    */
    private static function callback_return($func_return)
    {

        $reponse = new Response();

        if (is_string($func_return)) {
            $reponse->withHtml($func_return);
        }
        var_dump($func_return);
        exit;

    }

    protected static function abort()
    {
        http_response_code(404);
        die("404 page not found");
    }
}