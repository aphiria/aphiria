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
 * Defines a validator for an object
 */
final class ObjectValidator
{
    /** @var string The name of the class the validator is for */
    private string $class;
    /** @var FieldValidator[] The list of field validators for this object */
    private array $fieldValidators;

    /**
     * @param string $class The name of the class the validator is for
     * @param FieldValidator[] $fieldValidators The list of field validators for this object
     */
    public function __construct(string $class, array $fieldValidators)
    {
        $this->class = $class;
        $this->fieldValidators = $fieldValidators;
    }

    /**
     * Gets the name of the class that is validated by this validator
     *
     * @return string The name of the class
     */
    public function getClass(): string
    {
        return $this->class;
    }

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
        ?ErrorCollection &$errors,
        ValidationContext $validationContext = null
    ): bool {
        try {
            $this->validateField($object, $fieldName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

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
        ?ErrorCollection &$errors,
        ValidationContext $validationContext = null
    ): bool {
        try {
            $this->validateObject($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

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
    ): void {
        foreach ($this->fieldValidators as $fieldValidator) {
            if ($fieldValidator->getFieldName() === $fieldName) {
                $fieldValidator->validateField($object, $validationContext ?? new ValidationContext($object));

                return;
            }
        }
    }

    /**
     * Validates an object
     *
     * @param object $object The object to validate
     * @param ValidationContext|null $validationContext The context to perform validation in, or null if no context exists yet
     * @throws ValidationException Thrown if the input object was invalid
     */
    public function validateObject(object $object, ValidationContext $validationContext = null): void
    {
        $validationContext ??= new ValidationContext($object);

        foreach ($this->fieldValidators as $fieldValidator) {
            $fieldValidator->validateField($object, $validationContext);
        }
    }
}
