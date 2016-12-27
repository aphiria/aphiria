<?php
namespace Opulence\Router;

use Opulence\Router\Dispatchers\IDependencyResolver;
use Opulence\Router\Dispatchers\IMiddlewarePipeline;

/**
 * Defines the router
 */
class Router implements IRouter
{
    /** @var IDependencyResolver The dependency resolver */
    private $dependencyResolver = null;
    /** @var IMiddlewarePipeline The middleware pipeline */
    private $middlewarePipeline = null;
    /** @var RouteMap[] The list of route maps */
    private $routeMaps = [];
    
    public function __construct(IDependencyResolver $dependencyResolver, IMiddlewarePipeline $middlewarePipieline, array $routeMaps)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->middlewarePipeline = $middlewarePipieline;
        $this->routeMaps = $routeMaps;
    }
    
    public function route($request)
    {
        foreach ($this->routeMaps as $routeMap) {
            if ($routeMap->getParsedRoute()->tryMatch($request, $routeVars)) {
                // Todo: Need to use $routeVars, mixed with calls to $routeMap->getParsedRoute()->getDefaultValue() to send route vars to controller
                $controller = $routeMap->getController();
                
                return $controller($request);
            }
        }
        
        throw new HttpException(404);
    }
}