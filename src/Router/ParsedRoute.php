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
    
    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }
    
    public function isMatch($request, array &$pathVars, array $defaultPathVarValues = []) : bool
    {
        // Todo: Set $pathVars, use default path vars too
        foreach ($this->constraints as $constraint) {
            if (!$constraint->isMatch($request)) {
                return false;
            }
        }
        
        return true;
    }
}