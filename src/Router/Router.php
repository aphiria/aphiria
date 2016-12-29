<?php
namespace Opulence\Router;

/**
 * Defines the router
 */
class Router implements IRouter
{
    /** @var Route[] The list of routes */
    private $routes = [];
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
    
    public function route($request)
    {
        foreach ($this->routes as $route) {
            if ($route->isMatch($request)) {
                return $route->dispatch($request);
            }
        }
        
        throw new HttpException(404);
    }
}