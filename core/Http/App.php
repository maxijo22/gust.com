<?php

namespace Myframework\Http;

use Myframework\Http\Request;
use Myframework\Migration\BaseMigration;
use Myframework\Router\Route;
use Myframework\Middleware\MiddlewareStack;

class App
{
    private $container;
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/config.php');
        $builder->useAutowiring(true);
        // $builder->load(true);
        $this->container = $builder->build();
    }
    public function handleRequest(Request $request)
    {

        require_once BASE_PATH . "/src/routes.php";
        $middlewares = require_once BASE_PATH . "/src/middlewares.php";
        MiddlewareStack::set($middlewares);
        Route::setContainer($this->container);
        Route::dispatch($request);

    }

    public function migration(): BaseMigration
    {

        chdir($this->dir);
        $path = "src" . DS . "MIgrations";
        return new BaseMigration($path);
    }


    public function run()
    {
        $this->handleRequest(Request::createFromGlobal());
    }
}