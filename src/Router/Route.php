<?php
namespace Opulence\Router;

use Closure;
use InvalidArgumentException;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var Closure The action this route performs */
    private $action = null;
    /** @var string|null The name of this route */
    private $name = null;
    /** @var IRouteConstraints[] The list of constraints that applies to this route */
    private $constraints = [];
    /** @var RouteTemplate The path route template */
    private $pathTemplate = null;
    /** @var RouteTemplate The host route template */
    private $hostTemplate = null;
    
    public function __construct(Closure $action, array $constraints, RouteTemplate $pathTemplate, RouteTemplate $hostTemplate = null, string $name = null)
    {
        $this->action = $action;
        
        if (count($constraints) == 0) {
            throw new InvalidArgumentException("Constraints must not be empty");
        }
        
        $this->constraints = $constraints;
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->name = $name;
    }
    
    public function dispatch($request)
    {
        return $this->action($request);
    }
    
    public function getName() : ?string
    {
        return $this->name;
    }
    
    public function getUri(array $routeVars) : string
    {
        // Todo: Using the host and path templates, piece this together
    }
    
    public function isMatch($request) : bool
    {
        foreach ($this->constraints as $constraint) {
            if (!$constraint->isMatch($request)) {
                return false;
            }
        }
        
        return true;
    }
}