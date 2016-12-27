<?php
namespace Opulence\Router\Constraints;

use Opulence\Router\RouteVarDictionary;

/**
 * Defines the scheme constraint
 */
class SchemeConstraint implements IRouteConstraint
{
    /**
     * @inheritdoc
     */
    public function isMatch($request, RouteVarDictionary $routeVars) : bool
    {
        // Todo
    }
}