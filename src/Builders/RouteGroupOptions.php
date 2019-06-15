<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
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
    /** @var string The path template that applies to the entire group */
    public string $pathTemplate;
    /** @var string|null The host template that applies to the entire group */
    public ?string $hostTemplate;
    /** @var bool Whether or not the entire group is HTTPS-only */
    public bool $isHttpsOnly;
    /** @var IRouteConstraint[] The list of route constraints that applies to the entire group */
    public array $constraints;
    /** @var MiddlewareBinding[] The list of middleware bindings that applies to the entire group */
    public array $middlewareBindings;
    /** @var array The mapping of custom attribute names => values to match on for the entire group */
    public array $attributes;

    /**
     * @param string $pathTemplate The path template that applies to the entire group
     * @param string|null $hostTemplate The host template that applies to the entire group, or null
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param IRouteConstraint[] $constraints The list of route constraints that applies to the entire group
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings that applies to the entire group
     * @param array $attributes The mapping of custom attribute names => values to match on for the entire group
     */
    public function __construct(
        string $pathTemplate,
        ?string $hostTemplate = null,
        bool $isHttpsOnly = false,
        array $constraints = [],
        array $middlewareBindings = [],
        array $attributes = []
    ) {
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->constraints = $constraints;
        $this->middlewareBindings = $middlewareBindings;
        $this->attributes = $attributes;
    }
}
