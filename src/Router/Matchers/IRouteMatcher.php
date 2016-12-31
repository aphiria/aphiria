<?php
namespace Opulence\Router\Matchers;

/**
 * Defines the interface for route matchers to implement
 */
interface IRouteMatcher
{
    public function tryMatch($request, array $routes, MatchedRoute &$matchedRoute) : bool;
}