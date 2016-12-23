<?php
namespace Opulence\Router;

use Opulence\Router\Parsers\IRouteParser;

/**
 * Defines the route map builder registry
 */
class RouteMapBuilderRegistry
{
    private $routeMapBuilders = [];
    private $routeParser = null;
    
    public function __construct(IRouteParser $routeParser)
    {
        $this->routeParser = $routeParser;
    }
    
    public function buildAll() : array
    {
        $builtRouteMaps = [];
        
        foreach ($this->routeMapBuilders as $routeMapBuilder) {
            $builtRouteMaps[] = $routeMapBuilder->build();
        }
        
        return $builtRouteMaps;
    }
    
    public function map(Route $route) : RouteMapBuilder
    {
        $parsedRoute = $this->routeParser->parse($route);
        
        return new RouteMapBuilder($parsedRoute);
    }
}