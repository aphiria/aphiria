<?php
namespace Opulence\Router;

/**
 * Defines the route variable dictionary
 */
class RouteVarDictionary 
{
    /** @var array The mapping of route variable names to their values */
    private $routeVars = [];
    
    public function add(string $name, $value)
    {
        $this->routeVars[$name] = $value;
    }
    
    public function get(string $name, $defaultValue = null)
    {
        if (!isset($this->routeVars[$name])) {
            return $defaultValue;
        }
        
        return $this->routeVars[$name];
    }
}