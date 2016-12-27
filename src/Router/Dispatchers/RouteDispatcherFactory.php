<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\Dispatchers\IDependencyResolver;
use Opulence\Router\Dispatchers\IMiddlewarePipeline;

/**
 * Defines the route dispatcher factory
 */
class RouteDispatcherFactory implements IRouteDispatcherFactory
{
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver = null;
    /** @var IMiddlewarePipeline The middleware pipeline */
    private $middlewarePipeline = null;
    
    public function __construct(IDependencyResolver $dependencyResolver, IMiddlewarePipeline $middlewarePipieline)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->middlewarePipeline = $middlewarePipieline;
    }
    
    public function createRouteDispatcherFromClosure(Closure $closure, array $middleware) : Closure
    {
        // Todo
    }
    
    public function createRouteDispatcherFromController(string $controllerName, string $controllerMethodName, array $middleware) : Closure
    {
        // Todo
    }
}