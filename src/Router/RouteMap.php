<?php
namespace Opulence\Router;

/**
 * Defines a route map
 */
class RouteMap 
{
    private $parsedRoute = null;
    private $middleware = [];
    private $controller = null;
    private $name = null;
    
    public function __construct(ParsedRoute $parsedroute, callable $controller, array $middleware = [], string $name = null)
    {
        $this->parsedRoute = $parsedRoute;
        $this->controller = $controller;
        $this->middleware = $middleware;
        $this->name = $name;
    }
    
    public function getController() : callable
    {
        return $this->controller;
    }
    
    public function getMiddleware() : array
    {
        return $this->middleware;
    }
    
    public function getName() : ?string
    {
        return $this->name;
    }

    public function getParsedRoute() : ParsedRoute
    {
        return $this->parsedRoute;
    }
}