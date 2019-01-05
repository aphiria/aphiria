<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Constraints;

use Opulence\Routing\Matchers\MatchedRouteCandidate;

/**
 * Defines the interface for route constraints to implement
 */
interface IRouteConstraint
{
    /**
     * Attempts to match a route with certain constraints
     *
     * @param MatchedRouteCandidate $matchedRouteCandidate The matched route candidate
     * @param string $httpMethod The HTTP method
     * @param string $host The host to match
     * @param string $path The path to match
     * @param array $headers The headers to match
     * @return bool True if the route is a match, otherwise false
     */
    public function passes(
        MatchedRouteCandidate $matchedRouteCandidate,
        string $httpMethod,
        string $host,
        string $path,
        array $headers
    ): bool;
}