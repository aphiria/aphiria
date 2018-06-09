<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Constraints;

use Opulence\Routing\Route;

/**
 * Defines the interface for route constraints to implement
 */
interface IRouteConstraint
{
    /**
     * Attempts to match a route with certain constraints
     *
     * @param string $host The host to match
     * @param string $path The path to match
     * @param array $headers The headers to match
     * @param Route $route The route to match on
     * @return bool True if the route is a match, otherwise false
     */
    public function isMatch(string $host, string $path, array $headers, Route $route) : bool;
}
