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
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use LogicException;

/**
 * Defines the builder for object constraints
 */
final class ObjectConstraintsBuilder
{
    /** @var ObjectConstraintRegistry The registry we're going to build */
    private ObjectConstraintRegistry $objectConstraints;
    /** @var string|null The current class name */
    private ?string $currClassName = null;

    /**
     * @param ObjectConstraintRegistry|null $objectConstraints The constraints to add to, or null if building a new registry
     */
    public function __construct(ObjectConstraintRegistry $objectConstraints = null)
    {
        // TODO: This somewhat feels like a registrant class because build() does nothing.  Am I doing this right?
        // TODO: Add test to make sure same instance is returned in build()
        $this->objectConstraints = $objectConstraints ?? new ObjectConstraintRegistry();
    }

    /**
     * Builds the object constraints
     *
     * @return ObjectConstraintRegistry The built object constraints
     */
    public function build(): ObjectConstraintRegistry
    {
        return $this->objectConstraints;
    }

    /**
     * Starts building constraints for a particular class
     *
     * @param string $className The name of the class whose constraints we'll build
     * @return $this For chaining
     */
    public function class(string $className): self
    {
        $this->currClassName = $className;

        return $this;
    }

    /**
     * Adds constraints for a method
     *
     * @param string $methodName The name of the method we're adding constraints to
     * @param IConstraint[]|IConstraint $constraints The constraint or list of constraints for the method
     * @return $this For chaining
     * @throws LogicException Thrown if no class is set
     */
    public function hasMethodConstraints(string $methodName, $constraints): self
    {
        if ($this->currClassName === null) {
            throw new LogicException('Must call ' . self::class . '::class() before calling this method');
        }

        $this->objectConstraints->registerObjectConstraints($this->currClassName, [], [$methodName => $constraints]);

        return $this;
    }

    /**
     * Adds constraints for a property
     *
     * @param string $propertyName The name of the property we're adding constraints to
     * @param IConstraint[]|IConstraint $constraints The constraint or list of constraints for the property
     * @return $this For chaining
     * @throws LogicException Thrown if no class is set
     */
    public function hasPropertyConstraints(string $propertyName, $constraints): self
    {
        if ($this->currClassName === null) {
            throw new LogicException('Must call ' . self::class . '::class() before calling this method');
        }

        $this->objectConstraints->registerObjectConstraints($this->currClassName, [$propertyName => $constraints], []);

        return $this;
    }
}
