<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\RouteMap;

/**
 * Defines the interface for dispatchers to implement
 */
interface IRouteDispatcher
{
    public function dispatch(RouteMap $routeMap, array $pathVars, $request);
}