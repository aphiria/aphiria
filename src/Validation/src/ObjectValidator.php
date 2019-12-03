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
    /** @var PropertyValidator[] The list of property validators for this object */
    private array $propertyValidators;
    /** @var MethodValidator[] The list of method validators for this object */
    private array $methodValidators;

    /**
     * @param string $class The name of the class the validator is for
     * @param PropertyValidator[] $propertyValidators The list of property validators for this object
     * @param MethodValidator[] $methodValidators The list of method validators for this object
     */
    public function __construct(string $class, array $propertyValidators = [], array $methodValidators = [])
    {
        $this->class = $class;
        $this->propertyValidators = $propertyValidators;
        $this->methodValidators = $methodValidators;
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
        ?ErrorCollection &$errors,
        ValidationContext $validationContext = null
    ): bool {
        try {
            $this->validateMethod($object, $methodName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

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
    ): void {
        foreach ($this->methodValidators as $methodValidator) {
            if ($methodValidator->getMethodName() === $methodName) {
                $methodValidator->validateMethod($object, $validationContext ?? new ValidationContext($object));

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

        foreach ($this->propertyValidators as $propertyValidator) {
            $propertyValidator->validateProperty($object, $validationContext);
        }

        foreach ($this->methodValidators as $methodValidator) {
            $methodValidator->validateMethod($object, $validationContext);
        }
    }

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
    ): void {
        foreach ($this->propertyValidators as $propertyValidator) {
            if ($propertyValidator->getPropertyName() === $propertyName) {
                $propertyValidator->validateProperty($object, $validationContext ?? new ValidationContext($object));

                return;
            }
        }
    }
}
