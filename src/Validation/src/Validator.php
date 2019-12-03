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
    public function tryValidateField(
        object $object,
        string $fieldName,
        ErrorCollection &$errors = null,
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
    public function validateField(object $object, string $fieldName, ValidationContext $validationContext = null): void
    {
        if (($objectValidator = $this->objectValidators->getObjectValidator(\get_class($object))) === null) {
            return;
        }

        $objectValidator->validateField($object, $fieldName, $validationContext);
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
}
