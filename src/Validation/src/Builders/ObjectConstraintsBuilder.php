<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Builders;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;

/**
 * Defines the builder for object constraints
 */
final class ObjectConstraintsBuilder
{
    /** @var ObjectConstraints The constraints we are building */
    private ObjectConstraints $objectConstraints;

    /**
     * @param string $className The name of the class this is building constraints for
     */
    public function __construct(string $className)
    {
        $this->objectConstraints = new ObjectConstraints($className);
    }

    /**
     * Builds the object constraints
     *
     * @return ObjectConstraints The built object constraints
     */
    public function build(): ObjectConstraints
    {
        return $this->objectConstraints;
    }

    /**
     * Adds constraints for a method
     *
     * @param string $methodName The name of the method we're adding constraints to
     * @param IConstraint[]|IConstraint $constraints The constraint or list of constraints for the method
     * @return $this For chaining
     */
    public function hasMethodConstraints(string $methodName, $constraints): self
    {
        $this->objectConstraints->addMethodConstraint($methodName, $constraints);

        return $this;
    }

    /**
     * Adds constraints for a property
     *
     * @param string $propertyName The name of the property we're adding constraints to
     * @param IConstraint[]|IConstraint $constraints The constraint or list of constraints for the property
     * @return $this For chaining
     */
    public function hasPropertyConstraints(string $propertyName, $constraints): self
    {
        $this->objectConstraints->addPropertyConstraint($propertyName, $constraints);

        return $this;
    }
}
