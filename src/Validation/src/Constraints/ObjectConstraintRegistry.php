<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a registry of object constraints
 */
final class ObjectConstraintRegistry
{
    /** @var array The mapping of class names to props to constraints */
    private array $propertyConstraints = [];
    /** @var array The mapping of class names to methods to constraints */
    private array $methodConstraints = [];

    /**
     * Copies a registry into this one
     *
     * @param ObjectConstraintRegistry $objectConstraints The constraints to copy
     */
    public function copy(ObjectConstraintRegistry $objectConstraints): void
    {
        $this->propertyConstraints = $objectConstraints->propertyConstraints;
        $this->methodConstraints = $objectConstraints->methodConstraints;
    }

    /**
     * Gets all the method constraints
     *
     * @param string $className The name of the class whose method constraints we want
     * @return IConstraint[] The mapping of method names to constraints
     */
    public function getAllMethodConstraints(string $className): array
    {
        return $this->methodConstraints[$className] ?? [];
    }

    /**
     * Gets all the property constraints
     *
     * @param string $className The name of the class whose property constraints we want
     * @return IConstraint[] The mapping of property names to constraints
     */
    public function getAllPropertyConstraints(string $className): array
    {
        return $this->propertyConstraints[$className] ?? [];
    }

    /**
     * Gets all constraints for a particular method
     *
     * @param string $className The name of the class whose method constraints we want
     * @param string $methodName The name of the method whose constraints we want
     * @return IConstraint[] The list of constraints
     */
    public function getMethodConstraints(string $className, string $methodName): array
    {
        return $this->methodConstraints[$className][$methodName] ?? [];
    }

    /**
     * Gets all constraints for a particular property
     *
     * @param string $className The name of the class whose property constraints we want
     * @param string $propertyName The name of the property whose constraints we want
     * @return IConstraint[] The list of constraints
     */
    public function getPropertyConstraints(string $className, string $propertyName): array
    {
        return $this->propertyConstraints[$className][$propertyName] ?? [];
    }

    /**
     * Registers some object constraints
     *
     * @param string $className The name of the class whose constraints we're registering
     * @param IConstraint[] $propertyConstraints The mapping of property names to constraints
     * @param IConstraint[] $methodConstraints The mapping of method names to constraints
     */
    public function registerObjectConstraints(string $className, array $propertyConstraints, array $methodConstraints): void
    {
        if (!isset($this->propertyConstraints[$className])) {
            $this->propertyConstraints[$className] = [];
        }

        if (!isset($this->methodConstraints[$className])) {
            $this->methodConstraints[$className] = [];
        }

        foreach ($propertyConstraints as $propertyName => $propertyConstraint) {
            if (!isset($this->propertyConstraints[$className][$propertyName])) {
                $this->propertyConstraints[$className][$propertyName] = [];
            }

            $propertyConstraint = \is_array($propertyConstraint) ? $propertyConstraint : [$propertyConstraint];
            $this->propertyConstraints[$className][$propertyName] = [
                ...$this->propertyConstraints[$className][$propertyName],
                ...$propertyConstraint
            ];
        }

        foreach ($methodConstraints as $methodName => $methodConstraint) {
            if (!isset($this->methodConstraints[$className][$methodName])) {
                $this->methodConstraints[$className][$methodName] = [];
            }

            $methodConstraint = \is_array($methodConstraint) ? $methodConstraint : [$methodConstraint];
            $this->methodConstraints[$className][$methodName] = [
                ...$this->methodConstraints[$className][$methodName],
                ...$methodConstraint
            ];
        }
    }
}
