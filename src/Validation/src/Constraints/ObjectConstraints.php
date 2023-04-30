<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints;

/**
 * Defines a mapping of object properties/methods to constraints that must be passed to be considered a valid object
 */
final class ObjectConstraints
{
    /** @var array<string, list<IConstraint>> The mapping of method names to constraints */
    private array $methodConstraints = [];
    /** @var array<string, list<IConstraint>> The mapping of property names to constraints */
    private array $propertyConstraints = [];

    /**
     * @param class-string $className The name of the class whose constraints are represented here
     * @param array<string, list<IConstraint>|IConstraint> $propertyConstraints The mapping of property names to constraints
     * @param array<string, list<IConstraint>|IConstraint> $methodConstraints The mapping of method names to constraints
     */
    public function __construct(
        public readonly string $className,
        array $propertyConstraints = [],
        array $methodConstraints = []
    ) {
        foreach ($propertyConstraints as $propertyName => $propertyConstraint) {
            $this->addPropertyConstraint($propertyName, $propertyConstraint);
        }

        foreach ($methodConstraints as $methodName => $methodConstraint) {
            $this->addMethodConstraint($methodName, $methodConstraint);
        }
    }

    /**
     * Adds a constraint to a method
     *
     * @param string $methodName The name of the method to add constraints to
     * @param list<IConstraint>|IConstraint $constraint The constraint or list of constraints to add
     */
    public function addMethodConstraint(string $methodName, IConstraint|array $constraint): void
    {
        $this->methodConstraints[$methodName] = \is_array($constraint) ? $constraint : [$constraint];
    }

    /**
     * Adds a constraint to a property
     *
     * @param string $propertyName The name of the property to add constraints to
     * @param list<IConstraint>|IConstraint $constraint The constraint or list of constraints to add
     */
    public function addPropertyConstraint(string $propertyName, IConstraint|array $constraint): void
    {
        $this->propertyConstraints[$propertyName] = \is_array($constraint) ? $constraint : [$constraint];
    }

    /**
     * Gets all the method constraints
     *
     * @return array<string, list<IConstraint>> The mapping of method names to constraints
     */
    public function getAllMethodConstraints(): array
    {
        return $this->methodConstraints;
    }

    /**
     * Gets all the property constraints
     *
     * @return array<string, list<IConstraint>> The mapping of property names to constraints
     */
    public function getAllPropertyConstraints(): array
    {
        return $this->propertyConstraints;
    }

    /**
     * Gets all constraints for a particular method
     *
     * @param string $methodName The name of the method whose constraints we want
     * @return list<IConstraint> The list of constraints
     */
    public function getMethodConstraints(string $methodName): array
    {
        return $this->methodConstraints[$methodName] ?? [];
    }

    /**
     * Gets all constraints for a particular property
     *
     * @param string $propertyName The name of the property whose constraints we want
     * @return list<IConstraint> The list of constraints
     */
    public function getPropertyConstraints(string $propertyName): array
    {
        return $this->propertyConstraints[$propertyName] ?? [];
    }
}
