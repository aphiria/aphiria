<?php
namespace Opulence\Router\Constraints;

use Opulence\Router\RouteVarDictionary;

/**
 * Defines the route constraints
 */
interface IRouteConstraint
{
    public function isMatch($request, RouteVarDictionary $routeVars) : bool;
}