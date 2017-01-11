<?php
namespace Opulence\Router\Dispatchers;

use Closure;
use Opulence\Router\Dispatchers\IDependencyResolver;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;
use SuperClosure\SerializerInterface;

/**
 * Defines the route action factory
 */
class RouteActionFactory implements IRouteActionFactory
{
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver = null;
    /** @var SerializerInterface The serializer to use in the actions */
    private $serializer = null;

    /**
     * @param IDependencyResolver $dependencyResolver The dependency resolver to use in actions
     * @param SerializerInterface $serializer The action serializer
     */
    public function __construct(IDependencyResolver $dependencyResolver, SerializerInterface $serializer = null)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->serializer = $serializer ?? new Serializer(new AstAnalyzer());
    }

    public function createRouteActionFromClosure(Closure $closure) : RouteAction
    {
        // Create closure that takes in request and route vars
        // and either resolves dependencies or injects route vars into the closure
        // using reflection
    }

    public function createRouteActionFromController(string $controllerName, string $controllerMethodName) : RouteAction
    {
        // Create closure that takes in request and route vars,
        // resolves controller class, and injects route vars into method
    }
}