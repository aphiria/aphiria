<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
     * @param string $pathTemplate The path template that applies to the entire group
     * @param string|null $hostTemplate The host template that applies to the entire group, or null
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param IRouteConstraint[] $constraints The list of route constraints that applies to the entire group
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings that applies to the entire group
     * @param array $attributes The mapping of custom attribute names => values to match on for the entire group
     */
    public function __construct(
        public string $pathTemplate,
        public ?string $hostTemplate = null,
        public bool $isHttpsOnly = false,
        public array $constraints = [],
        public array $middlewareBindings = [],
        public array $attributes = []
    ) {
    }
}
