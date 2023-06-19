<?php
namespace Myframework\Middleware;
use Myframework\Http\Request;
use Closure;



interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next);
}
