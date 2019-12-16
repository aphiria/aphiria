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
 * Defines a constraint violation
 */
final class ConstraintViolation
{
    /** @var IValidationConstraint The constraint that was violated */
    private IValidationConstraint $constraint;
    /** @var mixed The invalid value */
    private $invalidValue;
    /** @var mixed|object The root value that was being validated */
    private $rootValue;
    /** @var string The name of the property that was being validated */
    private ?string $propertyName;
    /** @var string The name of the method that was being validated */
    private ?string $methodName;

    /**
     * @param IValidationConstraint $constraint The constraint that was violated
     * @param mixed $invalidValue The invalid value
     * @param mixed|object $rootValue The root value that was being validated
     * @param string|null $propertyName The name of the property that was being validated
     * @param string|null $methodName The name of the method that was being validated
     */
    public function __construct(
        IValidationConstraint $constraint,
        $invalidValue,
        $rootValue,
        string $propertyName = null,
        string $methodName = null
    ) {
        $this->constraint = $constraint;
        $this->invalidValue = $invalidValue;
        $this->rootValue = $rootValue;
        $this->propertyName = $propertyName;
        $this->methodName = $methodName;
    }

    /**
     * Gets the constraint that was violated
     *
     * @return IValidationConstraint The constraint that was violated
     */
    public function getConstraint(): IValidationConstraint
    {
        return $this->constraint;
    }

    /**
     * Gets the invalid value
     *
     * @return mixed The invalid value
     */
    public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    /**
     * Gets the name of the method that was being validated
     *
     * @return string|null The name of the method that was validated, or null if it was not a method
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * Gets the name of the property that was being validated
     *
     * @return string|null The name of the property that was validated, or null if it was not a property
     */
    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    /**
     * Gets the root value that was being validated
     *
     * @return mixed|object The root value
     */
    public function getRootValue()
    {
        return $this->rootValue;
    }
}
