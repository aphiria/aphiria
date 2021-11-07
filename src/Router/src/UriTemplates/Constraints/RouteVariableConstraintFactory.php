<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the route variable constraint factory
 */
final class RouteVariableConstraintFactory
{
    /** @var array<string, Closure(mixed...): IRouteVariableConstraint> The mapping of constraint slugs to factories */
    private array $factories = [];

    /**
     * Creates a constraint for the given slug
     *
     * @param string $slug The slug for the constraint to create
     * @param list<mixed> $parameters The list of params to pass into the factory
     * @return IRouteVariableConstraint An instance of the constraint
     * @throws InvalidArgumentException Thrown if there's no factory registered for the slug
     * @throws RuntimeException Thrown if the factory does not return an instance of a constraint
     */
    public function createConstraint(string $slug, array $parameters = []): IRouteVariableConstraint
    {
        if (!isset($this->factories[$slug])) {
            throw new InvalidArgumentException("No factory registered for constraint \"$slug\"");
        }

        $constraint = $this->factories[$slug](...$parameters);

        if (!$constraint instanceof IRouteVariableConstraint) {
            throw new RuntimeException(
                "Factory for constraint \"$slug\" does not return an instance of " . IRouteVariableConstraint::class
            );
        }

        return $constraint;
    }

    /**
     * Registers a factory for a constraint
     *
     * @param string $slug The slug to register a factory for
     * @param Closure(mixed...): IRouteVariableConstraint $factory The factory that accepts an optional list of parameters and returns a constraint instance
     */
    public function registerConstraintFactory(string $slug, Closure $factory): void
    {
        $this->factories[$slug] = $factory;
    }
}
