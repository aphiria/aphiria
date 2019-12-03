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

use Aphiria\Validation\Rules\Errors\ErrorCollection;
use Aphiria\Validation\Rules\IRule;

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
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @return bool True if the method was valid, otherwise false
     */
    public function tryValidateMethod(
        object $object,
        string $methodName,
        ErrorCollection &$errors = null,
        ValidationContext $validationContext = null
    ): bool;

    /**
     * Tries to validate an object
     *
     * @param object $object The object to validate
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @return bool True if the object was valid, otherwise false
     */
    public function tryValidateObject(
        object $object,
        ErrorCollection &$errors = null,
        ValidationContext $validationContext = null
    ): bool;

    /**
     * Tries to validate a property in an object
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @return bool True if the property was valid, otherwise false
     */
    public function tryValidateProperty(
        object $object,
        string $propertyName,
        ErrorCollection &$errors = null,
        ValidationContext $validationContext = null
    ): bool;

    /**
     * Tries to validate a single value
     *
     * @param mixed $value The value to validate
     * @param IRule[] $rules The list of rules to use
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @return bool True if the value was valid, otherwise false
     */
    public function tryValidateValue(
        $value,
        array $rules,
        ErrorCollection &$errors = null
    ): bool;

    /**
     * Validates a method in an object
     *
     * @param object $object The object whose method we're validating
     * @param string $methodName The name of the method to validate
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @throws ValidationException Thrown if the method was invalid
     */
    public function validateMethod(
        object $object,
        string $methodName,
        ValidationContext $validationContext = null
    ): void;

    /**
     * Validates an object
     *
     * @param object $object The object to validate
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @throws ValidationException Thrown if the input object was invalid
     */
    public function validateObject(object $object, ValidationContext $validationContext = null): void;

    /**
     * Validates a property in an object
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @throws ValidationException Thrown if the property was invalid
     */
    public function validateProperty(
        object $object,
        string $propertyName,
        ValidationContext $validationContext = null
    ): void;

    /**
     * Validates a single value against a list of rules
     *
     * @param mixed $value The value to validate
     * @param IRule[] $rules The list of rules to use
     * @throws ValidationException Thrown if the value was invalid
     */
    public function validateValue($value, array $rules): void;
}
