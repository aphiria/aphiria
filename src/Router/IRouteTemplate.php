<?php
namespace Opulence\Router;

/**
 * Defines the interface for route templates to implement
 */
interface IRouteTemplate
{
    public function buildTemplate(array &$values) : string;
    
    public function getDefaultRouteVars() : array;
    
    public function tryMatch(string $value, array &$routeVars = []) : bool;
}