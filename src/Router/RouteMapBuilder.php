<?php
namespace Opulence\Router;

/**
 * 
 */
class RouteMapBuilder
{
    private $parsedRoute = null;
    private $controllerClassName = null;
    private $controllerMethodName = null;
    private $callableController = null;
    private $middleware = [];
    private $name = null;
    
    public function __construct(ParsedRoute $parsedRoute)
    {
        $this->parsedRoute = $parsedRoute;
    }
    
    public function build() : RouteMap
    {
        // Todo: Figure out how to createa callable from controller class
        // This will require an IRouteDispatcher instance, which I'm not sure where I'd get
        // Doesn't feel right to inject this in the constructor
        return new RouteMap($this->parsedRoute, null, $this->middleware, $this->name);
    }
    
    public function toController(string $controllerClassName, string $controllerMethodName) : self
    {
        $this->controllerClassName = $controllerClassName;
        $this->controllerMethodName = $controllerMethodName;
        
        return $this;
    }
    
    public function toControllerCallable(callable $controller) : self
    {
        $this->callableController = $controller;
        
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