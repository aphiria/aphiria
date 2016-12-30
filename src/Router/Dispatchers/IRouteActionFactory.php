<?php
namespace Opulence\Router\Dispatchers;

use Closure;

/**
 * Defines the interface for route action factories to implement
 */
interface IRouteActionFactory
{
    public function createRouteActionFromClosure(Closure $closure) : Closure;
    
    public function createRouteActionFromController(string $controllerName, string $controllerMethodName) : Closure;
}