<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines the context that validation occurs in
 */
final class ValidationContext
{
    /** @var mixed The value being validated */
    private $value;
    /** @var string|null The name of the property being validated, or null if it wasn't a property */
    private ?string $propertyName;
    /** @var string|null The name of the method being validated, or null if it wasn't a method */
    private ?string $methodName;
    /** @var ValidationContext[] The list of child contexts, if there are any */
    private array $childContexts = [];
    /** @var ValidationContext|null The parent context, if there is one */
    private ?ValidationContext $parentContext;
    /** @var ConstraintViolation[] The list of constraint violations that occurred in this context */
    private array $constraintViolations = [];

    /**
     * @param mixed $value The value being validated
     * @param string|null $propertyName The name of the property being validated, or null if it wasn't a property
     * @param string|null $methodName The name of the method being validated, or null if it wasn't a method
     * @param ValidationContext|null $parentContext The parent context if there was one, otherwise null
     * @throws CircularDependencyException Thrown if a circular dependency was detected
     */
    public function __construct(
        $value,
        string $propertyName = null,
        string $methodName = null,
        ValidationContext $parentContext = null
    ) {
        $this->value = $value;
        $this->propertyName = $propertyName;
        $this->methodName = $methodName;
        $this->parentContext = $parentContext;

        if ($this->parentContext !== null) {
            $this->parentContext->addChildContext($this);
        }

        $this->validateNoCircularDependencies();
    }

    /**
     * Adds many constraint violations to the context
     *
     * @param ConstraintViolation[] $constraintViolations The violations to add
     */
    public function addManyConstraintViolations(array $constraintViolations): void
    {
        $this->constraintViolations = [...$this->constraintViolations, ...$constraintViolations];
    }

    /**
     * Adds a constraint violation to the context
     *
     * @param ConstraintViolation $constraintViolation The violation to add
     */
    public function addConstraintViolation(ConstraintViolation $constraintViolation): void
    {
        $this->constraintViolations[] = $constraintViolation;
    }

    /**
     * Gets the list of constraint violations
     *
     * @return ConstraintViolation[] The list of constraint violations
     */
    public function getConstraintViolations(): array
    {
        $allConstraintViolations = $this->constraintViolations;

        foreach ($this->childContexts as $childContext) {
            $allConstraintViolations = [...$allConstraintViolations, ...$childContext->getConstraintViolations()];
        }

        return $allConstraintViolations;
    }

    /**
     * Gets the error messages for all constraint violations
     *
     * @return string[] The list of error messages
     */
    public function getErrorMessages(): array
    {
        $errors = [];

        foreach ($this->getConstraintViolations() as $violation) {
            $errors[] = $violation->getErrorMessage();
        }

        return $errors;
    }

    /**
     * Gets the name of the method being validated
     *
     * @return string|null The name of the method being validated, or null if not a method
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * Gets the name of the property being validated
     *
     * @return string|null The name of the property being validated, or null if not a property
     */
    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    /**
     * Gets the top-most value in the context chain that was being validated
     *
     * @return mixed The root value
     */
    public function getRootValue()
    {
        if ($this->parentContext === null) {
            return $this->value;
        }

        return $this->parentContext->getRootValue();
    }

    /**
     * Gets the value being validated
     *
     * @return mixed The value being validated
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Adds a child context to this context
     *
     * @param ValidationContext $childContext The child context
     */
    private function addChildContext(ValidationContext $childContext): void
    {
        $this->childContexts[] = $childContext;
    }

    /**
     * Validates that there are no circular dependencies in the validation context chain
     *
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    private function validateNoCircularDependencies(): void
    {
        // We only check circular dependencies on contexts that are validating an object, not a property/method on an object
        if (!\is_object($this->value) || !$this->validatesObject($this->value)) {
            return;
        }

        // Check all ancestors to see if any of them validated this particular object
        $parentContext = $this->parentContext;

        while ($parentContext !== null) {
            if ($parentContext->validatesObject($this->value)) {
                throw new CircularDependencyException(
                    'Circular dependency on ' . \get_class($this->value) . ' detected'
                );
            }

            $parentContext = $parentContext->parentContext;
        }
    }

    /**
     * Checks if the context validates a particular object
     *
     * @param object $object The object to check
     * @return bool True if the context validates the input object, otherwise false
     */
    private function validatesObject(object $object): bool
    {
        /**
         * We only check for circular dependencies for contexts that are for the object itself, not a property nor method.
         * It's expected that an object would show up multiple times in property/method contexts, but it should not
         * show up as the value of a context chain more than once.
         */
        return $this->propertyName === null && $this->methodName === null && $this->value === $object;
    }
}
