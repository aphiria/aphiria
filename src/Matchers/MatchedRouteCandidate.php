<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Route;

/**
 * Defines a matched route candidate
 */
class MatchedRouteCandidate
{
    /** @var Route The route that was matched */
    public $route;
    /** @var array The mapping of route variable names to values */
    public $routeVariables;

    /**
     * @param Route $route The route that was matched
     * @param array $routeVariables The mapping of route variable names to values
     */
    public function __construct(Route $route, array $routeVariables)
    {
        $this->route = $route;
        $this->routeVariables = $routeVariables;
    }
}