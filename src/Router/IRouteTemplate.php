<?php
namespace Opulence\Router;

/**
 * Defines the interface for route templates to implement
 */
interface IRouteTemplate
{
    public function buildTemplate(array $routeVars) : string;

    public function tryMatch(string $value, array &$routeVars = []) : bool;
}
