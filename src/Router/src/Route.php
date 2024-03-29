<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\UriTemplates\UriTemplate;

/**
 * Defines a route
 */
final readonly class Route
{
    /**
     * @param UriTemplate $uriTemplate The raw URI template
     * @param RouteAction $action The action this route takes
     * @param list<IRouteConstraint> $constraints The list of constraints
     * @param list<MiddlewareBinding> $middlewareBindings The list of middleware bindings
     * @param string|null $name The name of this route
     * @param array<string, mixed> $parameters The mapping of custom parameter names => values
     */
    public function __construct(
        public UriTemplate $uriTemplate,
        public RouteAction $action,
        public array $constraints,
        public array $middlewareBindings = [],
        public ?string $name = null,
        public array $parameters = []
    ) {
    }
}
