<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\UriTemplates\UriTemplate;

/**
 * Defines a route
 */
final class Route
{
    /** @var UriTemplate The raw URI template */
    public UriTemplate $uriTemplate;
    /** @var RouteAction The action in the route */
    public RouteAction $action;
    /** @var IRouteConstraint[] The list of constraints on this route */
    public array $constraints;
    /** @var MiddlewareBinding[] The list of middleware bindings */
    public array $middlewareBindings;
    /** @var string|null The name of the route */
    public ?string $name;
    /** @var array The mapping of attribute names to values */
    public array $attributes;

    /**
     * @param UriTemplate $uriTemplate The raw URI template
     * @param RouteAction $action The action this route takes
     * @param IRouteConstraint[] $constraints The list of constraints
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     * @param string|null $name The name of this route
     * @param array $attributes The mapping of custom attribute names => values
     */
    public function __construct(
        UriTemplate $uriTemplate,
        RouteAction $action,
        array $constraints,
        array $middlewareBindings = [],
        string $name = null,
        array $attributes = []
    ) {
        $this->uriTemplate = $uriTemplate;
        $this->action = $action;
        $this->constraints = $constraints;
        $this->middlewareBindings = $middlewareBindings;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    /**
     * Performs a deep clone of object properties
     */
    public function __clone()
    {
        $this->action = clone $this->action;
        $this->uriTemplate = clone $this->uriTemplate;
        $clonedConstraints = [];

        foreach ($this->constraints as $constraint) {
            $clonedConstraints[] = clone $constraint;
        }

        $this->constraints = $clonedConstraints;
        $clonedMiddlewareBindings = [];

        foreach ($this->middlewareBindings as $middlewareBinding) {
            $clonedMiddlewareBindings[] = clone $middlewareBinding;
        }

        $this->middlewareBindings = $clonedMiddlewareBindings;
    }
}
