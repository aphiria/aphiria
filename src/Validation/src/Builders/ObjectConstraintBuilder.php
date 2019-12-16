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

use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use LogicException;

/**
 * Defines the constraint builder for objects
 */
final class ObjectConstraintBuilder
{
    /** @var string The name of the class this builder is for */
    private string $className;
    /** @var ConstraintRegistry The constraint registry to register constraints to */
    private ConstraintRegistry $constraints;
    /** @var string|null The current field (property/method) that we're adding constraints to */
    private ?string $currField = null;
    /** @var string|null The current field type ('property' or 'method') that we're adding constraints to */
    private ?string $currFieldType = null;

    /**
     * @param string $className The name of the class this builder is for
     * @param ConstraintRegistry $constraints The constraint registry to register constraints to
     */
    public function __construct(string $className, ConstraintRegistry $constraints)
    {
        $this->className = $className;
        $this->constraints = $constraints;
    }

    /**
     * Sets the current field to the input method name
     *
     * @param string $methodName The name of the method to add constraints to
     * @return $this For chaining
     */
    public function hasMethod(string $methodName): self
    {
        $this->currField = $methodName;
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
        $this->currField = $propertyName;
        $this->currFieldType = 'property';

        return $this;
    }

    /**
     * Adds a constraint to the current field
     *
     * @param IValidationConstraint $constraint The constraint to add
     * @return $this For chaining
     * @throws LogicException Thrown if the current field is not set
     */
    public function withConstraint(IValidationConstraint $constraint): self
    {
        return $this->withConstraints([$constraint]);
    }

    /**
     * Adds constraints to the current field
     *
     * @param IValidationConstraint[] $constraints The constraints to add
     * @return $this For chaining
     * @throws LogicException Thrown if the current field is not set
     */
    public function withConstraints(array $constraints): self
    {
        if ($this->currField === null) {
            throw new LogicException('Must call hasMethod() or hasProperty() before adding constraints');
        }

        if ($this->currFieldType === 'property') {
            $this->constraints->registerPropertyConstraints($this->className, $this->currField, $constraints);
        } elseif ($this->currFieldType === 'method') {
            $this->constraints->registerMethodConstraints($this->className, $this->currField, $constraints);
        }

        return $this;
    }
}
