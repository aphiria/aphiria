<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IConstraint;
use InvalidArgumentException;

/**
 * Defines the interface for validators to implement
 */
interface IValidator
{
    /**
     * Tries to validate a method in an object
     *
     * @param object $object The object whose method we're validating
     * @param string $methodName The name of the method to validate
     * @param array $violations The list of violations if there are any
     * @return bool True if the method was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function tryValidateMethod(object $object, string $methodName, array &$violations = []): bool;

    /**
     * Tries to validate an object
     *
     * @param object $object The object to validate
     * @param array $violations The list of violations if there are any
     * @return bool True if the object was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function tryValidateObject(object $object, array &$violations = []): bool;

    /**
     * Tries to validate a property in an object
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @param array $violations The list of violations if there are any
     * @return bool True if the property was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function tryValidateProperty(object $object, string $propertyName, array &$violations = []): bool;

    /**
     * Tries to validate a single value
     *
     * @param mixed $value The value to validate
     * @param IConstraint[] $constraints The list of constraints to use
     * @param array $violations The list of violations if there are any
     * @return bool True if the value was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function tryValidateValue($value, array $constraints, array &$violations = []): bool;

    /**
     * Validates a method in an object
     *
     * @param object $object The object whose method we're validating
     * @param string $methodName The name of the method to validate
     * @throws ValidationException Thrown if the method was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws InvalidArgumentException Thrown if the method does not exist
     */
    public function validateMethod(object $object, string $methodName): void;

    /**
     * Validates an object
     *
     * @param object $object The object to validate
     * @throws ValidationException Thrown if the input object was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function validateObject(object $object): void;

    /**
     * Validates a property in an object
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @throws ValidationException Thrown if the property was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws InvalidArgumentException Thrown if the property does not exist
     */
    public function validateProperty(object $object, string $propertyName): void;

    /**
     * Validates a single value against a list of constraints
     *
     * @param mixed $value The value to validate
     * @param IConstraint[] $constraints The list of constraints to use
     * @throws ValidationException Thrown if the value was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     */
    public function validateValue($value, array $constraints): void;
}
