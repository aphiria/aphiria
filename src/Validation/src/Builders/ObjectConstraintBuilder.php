<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Builders;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;
use LogicException;

/**
 * Defines the constraint builder for objects
 */
final class ObjectConstraintBuilder
{
    /** @var string The name of the class this builder is for */
    private string $className;
    /** @var string|null The current field (property/method) that we're adding constraints to */
    private ?string $currFieldName = null;
    /** @var string|null The current field type ('property' or 'method') that we're adding constraints to */
    private ?string $currFieldType = null;
    /** @var IConstraint[] The mapping of property names to constraints */
    private array $propertyConstraints = [];
    /** @var IConstraint[] The mapping of method names to constraints */
    private array $methodConstraints = [];

    /**
     * @param string $className The name of the class this builder is for
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Builds the object constraints
     *
     * @return ObjectConstraints The built object constraints
     */
    public function build(): ObjectConstraints
    {
        return new ObjectConstraints($this->className, $this->propertyConstraints, $this->methodConstraints);
    }

    /**
     * Sets the current field to the input method name
     *
     * @param string $methodName The name of the method to add constraints to
     * @return $this For chaining
     */
    public function hasMethod(string $methodName): self
    {
        $this->currFieldName = $methodName;
        $this->currFieldType = 'method';

        return $this;
    }

    /**
     * Sets the current field to the input property name
     *
     * @param string $propertyName The name of the property to add constraints to
     * @return $this For chaining
     */
    public function hasProperty(string $propertyName): self
    {
        $this->currFieldName = $propertyName;
        $this->currFieldType = 'property';

        return $this;
    }

    /**
     * Adds a constraint to the current field
     *
     * @param IConstraint $constraint The constraint to add
     * @return $this For chaining
     * @throws LogicException Thrown if the current field is not set
     */
    public function withConstraint(IConstraint $constraint): self
    {
        return $this->withConstraints([$constraint]);
    }

    /**
     * Adds constraints to the current field
     *
     * @param IConstraint[] $constraints The constraints to add
     * @return $this For chaining
     * @throws LogicException Thrown if the current field is not set
     */
    public function withConstraints(array $constraints): self
    {
        if ($this->currFieldName === null) {
            throw new LogicException('Must call hasMethod() or hasProperty() before adding constraints');
        }

        if ($this->currFieldType === 'property') {
            if (!isset($this->propertyConstraints[$this->currFieldName])) {
                $this->propertyConstraints[$this->currFieldName] = [];
            }

            $this->propertyConstraints[$this->currFieldName] = [...$this->propertyConstraints[$this->currFieldName], ...$constraints];
        } elseif ($this->currFieldType === 'method') {
            if (!isset($this->methodConstraints[$this->currFieldName])) {
                $this->methodConstraints[$this->currFieldName] = [];
            }

            $this->methodConstraints[$this->currFieldName] = [...$this->methodConstraints[$this->currFieldName], ...$constraints];
        }

        return $this;
    }
}
