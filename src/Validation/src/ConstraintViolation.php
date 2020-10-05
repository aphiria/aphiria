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
    /**
     * @param string $errorMessage The error message
     * @param IConstraint $constraint The constraint that was violated
     * @param mixed $invalidValue The invalid value
     * @param mixed $rootValue The root value that was being validated
     * @param string|null $propertyName The name of the property that was being validated
     * @param string|null $methodName The name of the method that was being validated
     */
    public function __construct(
        private string $errorMessage,
        private IConstraint $constraint,
        private mixed $invalidValue,
        private mixed $rootValue,
        private ?string $propertyName = null,
        private ?string $methodName = null
    ) {
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
    public function getInvalidValue(): mixed
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
     * @return mixed The root value
     */
    public function getRootValue(): mixed
    {
        return $this->rootValue;
    }
}
