<?php
namespace Opulence\Router;

/**
 * Defines a route template
 */
class RegexRouteTemplate
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
    
    public function getDefaultRouteVars() : array
    {
        return $this->defaultRouteVars;
    }
    
    public function tryMatch(string $value, array &$routeVars = []) : bool
    {
        
    }
}