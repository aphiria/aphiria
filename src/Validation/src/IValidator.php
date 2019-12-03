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

/**
 * Defines the interface for validators to implement
 */
interface IValidator
{
    /**
     * Tries to validate a field in an object
     *
     * @param object $object The object whose field we're validating
     * @param string $fieldName The name of the field to validate
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @return bool True if the field was valid, otherwise false
     */
    public function tryValidateField(
        object $object,
        string $fieldName,
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
     * Validates a field in an object
     *
     * @param object $object The object whose field we're validating
     * @param string $fieldName The name of the field to validate
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @throws ValidationException Thrown if the field was invalid
     */
    public function validateField(
        object $object,
        string $fieldName,
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
}
