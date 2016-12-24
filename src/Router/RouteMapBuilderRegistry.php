<?php
namespace Opulence\Router;

use Opulence\Router\Parsers\IRouteParser;

/**
 * Defines the route map builder registry
 */
class RouteMapBuilderRegistry
{
    /** @var RuteMapBuilder[] The list of registered route map builders */
    private $routeMapBuilders = [];
    /** @var IRouteParser The route parser */
    private $routeParser = null;
    /** @var RouteGroupOptions The stack of route group options */
    private $groupOptionsStack = [];
    
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
    
    public function group(RouteGroupOptions $groupOptions, callable $callable) : RouteMapGroupBuilder
    {
        array_push($this->groupOptionsStack, $groupOptions);
        $callable($this);
        array_pop($this->groupOptionsStack);
    }
    
    public function map(Route $route) : RouteMapBuilder
    {
        $parsedRoute = $this->routeParser->parse($this->applyGroupOptionsToRoute($route));
        $routeMapBuilder = $this->applyGroupOptionsToRouteBuilder(new RouteMapBuilder($parsedRoute));
        
        return $routeMapBuilder;
    }
    
    private function applyGroupOptionsToRoute(Route $route) : Route
    {
        $groupRawPath = "";
        $groupRawHost = "";
        $groupIsHttpsOnly = false;

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupRawPath .= $groupOptions->getRawPathPrefix();
            $groupRawHost = $groupOptions->getRawHost() . $groupRawHost;
            $groupIsHttpsOnly = $groupIsHttpsOnly || $groupOptions->isHttpsOnly();
        }

        $routeRawPath = $groupRawPath . $route->getRawPath();
        $routeRawHost = $groupRawHost . $route->getRawHost();
        
        return new Route($route->getMethods(), $routeRawPath, $routeRawHost, $groupIsHttpsOnly);
    }
    
    private function applyGroupOptionsToRouteBuilder(RouteMapBuilder $routeMapBuilder) : RouteMapBuilder
    {
        $groupMiddleware = [];
        
        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupMiddleware = array_merge($groupMiddleware, $groupOptions->getMiddleware());
        }
        
        $routeMapBuilder->withMiddleware($groupMiddleware);
        
        return $routeMapBuilder;
    }
}