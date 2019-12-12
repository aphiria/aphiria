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
 * Defines the constraint factory
 */
final class RouteVariableConstraintFactory implements IRouteVariableConstraintFactory
{
    /** @var Closure[] The mapping of constraint slugs to factories */
    private array $factories = [];

    /**
     * @inheritdoc
     */
    public function createConstraint(string $slug, array $params = []): IRouteVariableConstraint
    {
        if (!isset($this->factories[$slug])) {
            throw new InvalidArgumentException("No factory registered for constraint \"$slug\"");
        }

        $constraint = $this->factories[$slug](...$params);

        if (!$constraint instanceof IRouteVariableConstraint) {
            throw new RuntimeException(
                "Factory for constraint \"$slug\" does not return an instance of " . IRouteVariableConstraint::class
            );
        }

        return $constraint;
    }

    public function registerConstraintFactory(string $slug, Closure $factory): void
    {
        $this->factories[$slug] = $factory;
    }
}
