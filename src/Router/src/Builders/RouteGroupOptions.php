<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;

/**
 * Defines the route group options
 */
class RouteGroupOptions
{
    /**
     * @param string $path The path template that applies to the entire group
     * @param string|null $host The host template that applies to the entire group, or null
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param list<IRouteConstraint> $constraints The list of route constraints that applies to the entire group
     * @param list<MiddlewareBinding> $middlewareBindings The list of middleware bindings that applies to the entire group
     * @param array<string, mixed> $parameters The mapping of custom parameter names => values to match on for the entire group
     */
    public function __construct(
        public readonly string $path,
        public readonly ?string $host = null,
        public readonly bool $isHttpsOnly = false,
        public array $constraints = [],
        public array $middlewareBindings = [],
        public readonly array $parameters = []
    ) {
    }
}
