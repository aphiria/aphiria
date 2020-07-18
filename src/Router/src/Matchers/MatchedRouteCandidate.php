<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers;

use Aphiria\Routing\Route;

/**
 * Defines a matched route candidate
 */
final class MatchedRouteCandidate
{
    /** @var Route The route that was matched */
    public Route $route;
    /** @var array The mapping of route variable names to values */
    public array $routeVariables;

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
