<?php
namespace Opulence\Router;

use Closure;
use LogicException;
use Opulence\Router\Dispatchers\IRouteDispatcherFactory;

/**
 * Defines the route map builder
 */
class RouteMapBuilder
{
    /** @var IRouteDispatcherFactory The route dispatcher factory */
    private $routeDispatcherFactory = null;
    /** @var ParsedRoute The parsed route */
    private $parsedRoute = null;
    /** @var string The name of the controller class to map to */
    private $controllerClassName = "";
    /** @var string The name of the method in the controller class to map to */
    private $controllerMethodName = "";
    /** @var Closure The callback to map to */
    private $closureController = null;
    /** @var array The list of middleware on this route */
    private $middleware = [];
    /** @var string|null The name of this route */
    private $name = null;
    
    public function __construct(IRouteDispatcherFactory $routeDispatcherFactory, ParsedRoute $parsedRoute)
    {
        $this->routeDispatcherFactory = $routeDispatcherFactory;
        $this->parsedRoute = $parsedRoute;
    }
    
    public function build() : RouteMap
    {
        if ($this->closureController instanceof Closure) {
            $controller = $this->routeDispatcherFactory->createRouteDispatcherFromClosure($this->closureController, $this->middleware);
        } elseif (strlen($this->controllerClassName) > 0 && strlen($this->controllerMethodName) > 0) {
            $controller = $this->routeDispatcherFactory->createRouteDispatcherFromController($this->controllerClassName, $this->controllerMethodName, $this->middleware);
        } else {
            throw new LogicException("No controller set");
        }
        
        return new RouteMap($this->parsedRoute, $controller, $this->name);
    }
    
    public function toClosure(Closure $controller) : self
    {
        $this->closureController = $controller;
        
        return $this;
    }
    
    public function toController(string $controllerClassName, string $controllerMethodName) : self
    {
        $this->controllerClassName = $controllerClassName;
        $this->controllerMethodName = $controllerMethodName;
        
        return $this;
    }
    
    public function withMiddleware($middleware) : self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        
        return $this;
    }
    
    public function withName(string $name) : self
    {
        $this->name = $name;
        
        return $this;
    }
}