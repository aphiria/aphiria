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

use Aphiria\Validation\Constraints\IConstraint;

/**
 * Defines a constraint violation
 */
final class ConstraintViolation
{
    /** @var string The error message */
    private string $errorMessage;
    /** @var IConstraint The constraint that was violated */
    private IConstraint $constraint;
    /** @var mixed The invalid value */
    private $invalidValue;
    /** @var mixed|object The root value that was being validated */
    private $rootValue;
    /** @var string The name of the property that was being validated */
    private ?string $propertyName;
    /** @var string The name of the method that was being validated */
    private ?string $methodName;

    /**
     * @param string $errorMessage The error message
     * @param IConstraint $constraint The constraint that was violated
     * @param mixed $invalidValue The invalid value
     * @param mixed|object $rootValue The root value that was being validated
     * @param string|null $propertyName The name of the property that was being validated
     * @param string|null $methodName The name of the method that was being validated
     */
    public function __construct(
        string $errorMessage,
        IConstraint $constraint,
        $invalidValue,
        $rootValue,
        string $propertyName = null,
        string $methodName = null
    ) {
        $this->errorMessage = $errorMessage;
        $this->constraint = $constraint;
        $this->invalidValue = $invalidValue;
        $this->rootValue = $rootValue;
        $this->propertyName = $propertyName;
        $this->methodName = $methodName;
    }

    /**
     * Gets the constraint that was violated
     *
     * @return IConstraint The constraint that was violated
     */
    public function getConstraint(): IConstraint
    {
        return $this->constraint;
    }

    /**
     * Gets the error message
     *
     * @return string The error message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
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
