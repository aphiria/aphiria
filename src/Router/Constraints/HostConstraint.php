<?php
namespace Opulence\Router\Constraints;

use Opulence\Router\RouteVarDictionary;

/**
 * Defines the host constraint
 */
class HostConstraint implements IRouteConstraint
{
    /**
     * @inheritdoc
     */
    public function isMatch($request, RouteVarDictionary $routeVars) : bool
    {
        // Todo
    }
}