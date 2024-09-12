<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

/**
 * Defines the context that validation occurs in
 */
final class ValidationContext
{
    /** @var list<ConstraintViolation> The list of constraint violations that occurred in this context and all child contexts */
    public array $constraintViolations {
        get {
            $allConstraintViolations = $this->constraintViolationsInThisContext;

            foreach ($this->childContexts as $childContext) {
                $allConstraintViolations = [...$allConstraintViolations, ...$childContext->constraintViolations];
            }

            return $allConstraintViolations;
        }
    }
    /** @var list<string> The list of error messages from all constraint violations */
    public array $errorMessages {
        get {
            $errorMessages = [];

            foreach ($this->constraintViolations as $violation) {
                $errorMessages[] = $violation->errorMessage;
            }

            return $errorMessages;
        }
    }
    /** @var mixed The top-most value in the context chain that was being validated */
    public mixed $rootValue {
        get => $this->parentContext === null ? $this->value : $this->parentContext->value;
    }
    /** @var list<ValidationContext> The list of child contexts, if there are any */
    private array $childContexts = [];
    /** @var list<ConstraintViolation> The list of constraint violations that occurred in this context */
    private array $constraintViolationsInThisContext = [];

    /**
     * @param mixed $value The value being validated
     * @param string|null $propertyName The name of the property being validated, or null if it wasn't a property
     * @param string|null $methodName The name of the method being validated, or null if it wasn't a method
     * @param ValidationContext|null $parentContext The parent context if there was one, otherwise null
     * @throws CircularDependencyException Thrown if a circular dependency was detected
     */
    public function __construct(
        public readonly mixed $value,
        public readonly ?string $propertyName = null,
        public readonly ?string $methodName = null,
        private readonly ?ValidationContext $parentContext = null
    ) {
        $this->parentContext?->addChildContext($this);
        $this->validateNoCircularDependencies();
    }

    /**
     * Adds a constraint violation to the context
     *
     * @param ConstraintViolation $constraintViolation The violation to add
     */
    public function addConstraintViolation(ConstraintViolation $constraintViolation): void
    {
        $this->constraintViolationsInThisContext[] = $constraintViolation;
    }

    /**
     * Adds many constraint violations to the context
     *
     * @param list<ConstraintViolation> $constraintViolations The violations to add
     */
    public function addManyConstraintViolations(array $constraintViolations): void
    {
        $this->constraintViolationsInThisContext = [...$this->constraintViolationsInThisContext, ...$constraintViolations];
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
                    'Circular dependency on ' . $this->value::class . ' detected'
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
