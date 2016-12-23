<?php
namespace Opulence\Router\Constraints;

/**
 * Defines the route constraints
 */
interface IRouteConstraint
{
    public function isMatch($request) : bool;
}