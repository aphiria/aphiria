<?php
namespace Opulence\Router\Dispatchers;

use Closure;

/**
 * Defines the interface for route dispatcher factories to implement
 */
interface IRouteDispatcherFactory
{
    public function createRouteDispatcherFromClosure(Closure $closure, array $middleware) : Closure;
    
    public function createRouteDispatcherFromController(string $controllerName, string $controllerMethodName, array $middleware) : Closure;
}