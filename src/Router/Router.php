<?php
namespace Opulence\Router;

/**
 * Defines the router
 */
class Router implements IRouter
{
    private $routeMaps = [];
    
    public function __construct(array $routeMaps)
    {
        $this->routeMaps = $routeMaps;
    }
    
    public function route($request)
    {
        
    }
}