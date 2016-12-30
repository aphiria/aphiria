<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\Dispatchers\IDependencyResolver;

/**
 * Defines the route action factory
 */
class RouteActionFactory implements IRouteActionFactory
{
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver = null;
    
    public function __construct(IDependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }
    
    public function createRouteActionFromClosure(Closure $closure) : Closure
    {
        // Create closure that takes in request and route vars
        // and either resolves dependencies or injects route vars into the closure
        // using reflection
    }
    
    public function createRouteActionFromController(string $controllerName, string $controllerMethodName) : Closure
    {
        // Create closure that takes in request and route vars,
        // resolves controller class, and injects route vars into method
    }
}