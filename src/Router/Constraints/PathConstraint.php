<?php
namespace Opulence\Router\Constraints;

use Opulence\Router\RouteVarDictionary;

/**
 * Defines the path constraint
 */
class PathConstraint implements IRouteConstraint
{
    /**
     * @inheritdoc
     */
    public function isMatch($request, RouteVarDictionary $routeVars) : bool
    {
        // Todo
    }
}