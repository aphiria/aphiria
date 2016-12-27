<?php
namespace Opulence\Router;

use Closure;
use Opulence\Router\RouteVarDictionary;

/**
 * Defines a route map
 */
class RouteMap 
{
    private $parsedRoute = null;
    private $controller = null;
    private $name = null;
    
    public function __construct(ParsedRoute $parsedroute, Closure $controller, string $name = null)
    {
        $this->parsedRoute = $parsedRoute;
        $this->controller = $controller;
        $this->name = $name;
    }
    
    public function dispatch($request, RouteVarDictionary $routeVars)
    {
        return $this->controller($request, $routeVars);
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