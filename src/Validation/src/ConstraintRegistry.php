<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IValidationConstraint;

/**
 * Defines the registry of constraints for various object properties and methods
 */
final class ConstraintRegistry
{
    /** @var array The mapping of class names to an array of property names and constraints */
    private array $classesToPropertyConstraints = [];
    /** @var array The mapping of class names to an array of method names and constraints */
    private array $classesToMethodConstraints = [];

    /**
     * Gets the constraints associated with a particular method
     *
     * @param string $className The name of the class that contains the method
     * @param string $methodName The name of the method whose constraints we want
     * @return IValidationConstraint[] The list of constraints for the method
     */
    public function getMethodConstraints(string $className, string $methodName): array
    {
        if (!isset($this->classesToMethodConstraints[$className][$methodName])) {
            return [];
        }

        return $this->classesToMethodConstraints[$className][$methodName];
    }

    /**
     * Gets the constraints associated with all methods
     *
     * @param string $className The name of the class to search
     * @return IValidationConstraint[] The mapping of method names to constraints
     */
    public function getAllMethodConstraints(string $className): array
    {
        if (!isset($this->classesToMethodConstraints[$className])) {
            return [];
        }

        return $this->classesToMethodConstraints[$className];
    }

    /**
     * Gets the constraints associated with all properties
     *
     * @param string $className The name of the class to search
     * @return IValidationConstraint[] The mapping of property names to constraints
     */
    public function getAllPropertyConstraints(string $className): array
    {
        if (!isset($this->classesToPropertyConstraints[$className])) {
            return [];
        }

        return $this->classesToPropertyConstraints[$className];
    }

    /**
     * Gets the constraints associated with a particular property
     *
     * @param string $className The name of the class that contains the property
     * @param string $propertyName The name of the property whose constraints we want
     * @return IValidationConstraint[] The list of constraints for the property
     */
    public function getPropertyConstraints(string $className, string $propertyName): array
    {
        if (!isset($this->classesToPropertyConstraints[$className][$propertyName])) {
            return [];
        }

        return $this->classesToPropertyConstraints[$className][$propertyName];
    }

    /**
     * Registers constraints for a particular class method
     *
     * @param string $className The name of the class that contains the method
     * @param string $methodName The name of the method whose constraints we're registering
     * @param IValidationConstraint[]|IValidationConstraint $constraints The constraint or list of constraints to register
     */
    public function registerMethodConstraints(string $className, string $methodName, $constraints): void
    {
        if (!\is_array($constraints)) {
            $constraints = [$constraints];
        }

        if (!isset($this->classesToMethodConstraints[$className])) {
            $this->classesToMethodConstraints[$className] = [];
        }

        $this->classesToMethodConstraints[$className][$methodName] = $constraints;
    }

    /**
     * Registers constraints for a particular class property
     *
     * @param string $className The name of the class that contains the property
     * @param string $propertyName The name of the property whose constraints we're registering
     * @param IValidationConstraint[]|IValidationConstraint $constraints The constraint or list of constraints to register
     */
    public function registerPropertyConstraints(string $className, string $propertyName, $constraints): void
    {
        if (!\is_array($constraints)) {
            $constraints = [$constraints];
        }

        if (!isset($this->classesToPropertyConstraints[$className])) {
            $this->classesToPropertyConstraints[$className] = [];
        }

        $this->classesToPropertyConstraints[$className][$propertyName] = $constraints;
    }
}
