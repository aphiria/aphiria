<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the interface for route variable constraints to implement
 */
interface IRouteVariableConstraintFactory
{
    /**
     * Creates a constraint for the given slug
     *
     * @param string $slug The slug for the constraint to create
     * @param array $params The list of params to pass into the factory
     * @return IRouteVariableConstraint An instance of the constraint
     * @throws InvalidArgumentException Thrown if there's no factory registered for the slug
     * @throws RuntimeException Thrown if the factory does not return an instance of a constraint
     */
    public function createConstraint(string $slug, array $params = []): IRouteVariableConstraint;

    /**
     * Registers a factory for a constraint
     *
     * @param string $slug The slug to register a factory for
     * @param Closure $factory The factory that accepts an optional list of parameters and returns a constraint instance
     */
    public function registerConstraintFactory(string $slug, Closure $factory): void;
}
