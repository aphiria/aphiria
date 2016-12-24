<?php
namespace Opulence\Router;

/**
 * Defines the router
 */
class Router implements IRouter
{
    /** @var RouteMap[] The list of route maps */
    private $routeMaps = [];
    
    public function __construct(array $routeMaps)
    {
        $this->routeMaps = $routeMaps;
    }
    
    public function route($request)
    {
        foreach ($this->routeMaps as $routeMap) {
            if ($routeMap->getParsedRoute()->isMatch($request)) {
                $controller = $routeMap->getController();
                
                return $controller($request);
            }
        }
        
        throw new HttpException(404);
    }
}