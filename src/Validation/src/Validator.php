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
 * Defines the validator
 */
final class Validator implements IValidator
{
    /** @var ObjectValidatorRegistry The registry of object validators */
    private ObjectValidatorRegistry $objectValidators;

    /**
     * @param ObjectValidatorRegistry $objectValidators The registry of object validators
     */
    public function __construct(ObjectValidatorRegistry $objectValidators)
    {
        $this->objectValidators = $objectValidators;
    }

    /**
     * @inheritdoc
     */
    public function tryValidateMethod(
        object $object,
        string $methodName,
        ErrorCollection &$errors = null,
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
     * @inheritdoc
     */
    public function tryValidateObject(
        object $object,
        ErrorCollection &$errors = null,
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
     * @inheritdoc
     */
    public function tryValidateProperty(
        object $object,
        string $propertyName,
        ErrorCollection &$errors = null,
        ValidationContext $validationContext = null
    ): bool {
        try {
            $this->validateProperty($object, $propertyName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateValue(
        $value,
        array $rules,
        ErrorCollection &$errors = null,
        ValidationContext $validationContext = null
    ): bool {
        try {
            $this->validateValue($value, $rules);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateMethod(object $object, string $methodName, ValidationContext $validationContext = null): void
    {
        if (($objectValidator = $this->objectValidators->getObjectValidator(\get_class($object))) === null) {
            return;
        }

        $objectValidator->validateMethod($object, $methodName, $validationContext);
    }

    /**
     * @inheritdoc
     */
    public function validateObject(object $object, ValidationContext $validationContext = null): void
    {
        if (($objectValidator = $this->objectValidators->getObjectValidator(\get_class($object))) === null) {
            return;
        }

        $objectValidator->validateObject($object, $validationContext);
    }

    /**
     * @inheritdoc
     */
    public function validateProperty(object $object, string $propertyName, ValidationContext $validationContext = null): void
    {
        if (($objectValidator = $this->objectValidators->getObjectValidator(\get_class($object))) === null) {
            return;
        }

        $objectValidator->validateProperty($object, $propertyName, $validationContext);
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value, array $rules): void
    {
        // TODO: There is no object in this case.  What would my validation context be used for, then?  My rules depend on one.
        $validationContext = new ValidationContext();

        foreach ($rules as $rule) {
            $rule->passes($value, $validationContext);
        }
    }
}
