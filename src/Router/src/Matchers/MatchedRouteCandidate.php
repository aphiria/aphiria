<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Matchers;

use Aphiria\Routing\Route;

/**
 * Defines a matched route candidate
 */
final class MatchedRouteCandidate
{
    /**
     * @param Route $route The route that was matched
     * @param array<string, mixed> $routeVariables The mapping of route variable names to values
     */
    public function __construct(public readonly Route $route, public readonly array $routeVariables)
    {
    }
}
