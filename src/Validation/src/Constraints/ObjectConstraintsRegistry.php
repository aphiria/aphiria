<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a registry of object constraints
 */
final class ObjectConstraintsRegistry
{
    /** @var array<class-string, ObjectConstraints> The mapping of class names to object constraints */
    private array $objectConstraints = [];

    /**
     * Copies a registry into this one
     *
     * @param ObjectConstraintsRegistry $objectConstraints The constraints to copy
     * @internal
     */
    public function copy(ObjectConstraintsRegistry $objectConstraints): void
    {
        $this->objectConstraints = $objectConstraints->objectConstraints;
    }

    /**
     * Gets the list of constraints for a particular class
     *
     * @param class-string $className The name of the class whose constraints we want
     * @return ObjectConstraints|null The constraints, if there were any, otherwise null
     */
    public function getConstraintsForClass(string $className): ?ObjectConstraints
    {
        return $this->objectConstraints[$className] ?? null;
    }

    /**
     * Registers some object constraints
     *
     * @param ObjectConstraints $constraints The constraints to register
     */
    public function registerObjectConstraints(ObjectConstraints $constraints): void
    {
        $this->objectConstraints[$constraints->className] = $constraints;
    }
}
