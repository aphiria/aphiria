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
    /** @var ValidationContext|null The parent context, if there is one */
    private ?ValidationContext $parentContext;
    /** @var RuleViolation[] The list of rule violations that occurred in this context */
    private array $ruleViolations = [];
    /** @var string[] The list of object hash IDs we'll use to detect circular dependencies */
    private array $objectHashIds = [];

    /**
     * @param mixed $value The value being validated
     * @param string|null $propertyName The name of the property being validated, or null if it wasn't a property
     * @param string|null $methodName The name of the method being validated, or null if it wasn't a method
     * @param ValidationContext|null $parentContext The parent context, if there is one
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

        // Only add this object to the map if it is being validated - not any of its properties or methods
        if (\is_object($this->value) && $this->propertyName === null && $this->methodName === null) {
            // Purposely check for a circular dependency first, then add the hash
            if ($this->containsCircularDependency($this->value)) {
                throw new CircularDependencyException('Circular dependency on ' . \get_class($value) . ' detected');
            }

            $this->objectHashIds[\spl_object_hash($this->value)] = true;
        }
    }

    /**
     * Adds many rule violations to the context
     *
     * @param RuleViolation[] $ruleViolations The violations to add
     */
    public function addManyRuleViolations(array $ruleViolations): void
    {
        $this->ruleViolations = [...$this->ruleViolations, ...$ruleViolations];
    }

    /**
     * Adds a rule violation to the context
     *
     * @param RuleViolation $ruleViolation The violation to add
     */
    public function addRuleViolation(RuleViolation $ruleViolation): void
    {
        $this->ruleViolations[] = $ruleViolation;
    }

    /**
     * Checks if the context or any of its ancestors contains a circular dependency on the input object
     *
     * @param object $object The object to check for
     * @return bool True if the context (or its ancestors) contain a circular dependency, otherwise false
     */
    public function containsCircularDependency(object $object): bool
    {
        return isset($this->objectHashIds[\spl_object_hash($object)])
            || ($this->parentContext !== null && $this->parentContext->containsCircularDependency($object));
    }

    /**
     * Gets the root value that was being validated
     *
     * @return mixed The root value
     */
    public function getRootValue()
    {
        return $this->parentContext === null ? $this->value : $this->parentContext->getRootValue();
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
     * Gets the list of rule violations
     *
     * @return RuleViolation[] The list of rule violations
     */
    public function getRuleViolations(): array
    {
        $allRuleViolations = $this->ruleViolations;

        if ($this->parentContext !== null) {
            $allRuleViolations = [...$this->parentContext->getRuleViolations(), $allRuleViolations];
        }

        return $allRuleViolations;
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
}
