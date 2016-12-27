<?php
namespace Opulence\Router;

use Opulence\Router\Constraints\IRouteConstraint;

/**
 * Defines a parsed route
 */
class ParsedRoute 
{
    /** @var IRouteConstraints[] The list of constraints that applies to this route */
    private $constraints = [];
    /** @var array The list of default values for route variables */
    private $defaultValues = [];
    
    public function __construct(array $constraints, array $defaultValues = [])
    {
        $this->constraints = $constraints;
        $this->defaultValues = $defaultValues;
    }
    
    public function getDefaultValue(string $name)
    {
        if (isset($this->defaultValues[$variableName])) {
            return $this->defaultValues[$variableName];
        }

        return null;
    }
    
    public function tryMatch($request, RouteVarDictionary &$routeVars) : bool
    {
        $routeVars = new RouteVarDictionary();
        
        // Todo: Set $pathVars, use default path vars too
        foreach ($this->constraints as $constraint) {
            if (!$constraint->isMatch($request, $routeVars)) {
                return false;
            }
        }
        
        return true;
    }
}