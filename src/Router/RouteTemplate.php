<?php
namespace Opulence\Router;

/**
 * Defines a route template
 */
class RouteTemplate
{
    private $pathRegex = "";
    private $hostRegex = null;
    private $defaultRouteVars = [];
    
    public function __construct(string $pathRegex, string $hostRegex = null, array $defaultRouteVars = [])
    {
        $this->pathRegex = $pathRegex;
        $this->hostRegex = $hostRegex;
        $this->defaultRouteVars = $defaultRouteVars;
    }
    
    public function buildTemplate(array &$values) : string
    {
        
    }
    
    public function getDefaultVars() : array
    {
        
    }
    
    public function isMatch(string $value) : bool
    {
        
    }
}