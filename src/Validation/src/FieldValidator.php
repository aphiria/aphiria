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
use Closure;

/**
 * Defines a validator for an individual field
 */
final class FieldValidator
{
    /** @var string The name of the field (must match the name of the property if it's an object property) */
    private string $fieldName;
    /** @var Closure The closure that will yield the field value (should take in an object of the type that the field belongs to) */
    private Closure $fieldAccessor;
    /** @var IRule[] The list of rules to enforce on the field */
    private array $fieldRules;

    /**
     * @param string $fieldName The name of the field (must match the name of the property if it's an object property)
     * @param Closure $fieldAccessor The closure that will yield the field value (should take in an object of the type that the field belongs to)
     * @param IRule[] $rules The list of rules to enforce on the field
     */
    public function __construct(string $fieldName, Closure $fieldAccessor, array $rules)
    {
        $this->fieldName = $fieldName;
        $this->fieldAccessor = $fieldAccessor;
        $this->fieldRules = $rules;
    }

    /**
     * Gets the name of the field being validated
     *
     * @return string The name of the field
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Tries to validate a field on an object
     *
     * @param object $object The object the field value should come from
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the field was valid, otherwise false
     */
    public function tryValidateField(
        object $object,
        ?ErrorCollection &$errors,
        ValidationContext $validationContext
    ): bool
    {
        try {
            $this->validateField($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

    /**
     * Validates a field on an object
     *
     * @param object $object The object the field value should come from
     * @param ValidationContext $validationContext The context to perform validation in
     * @throws ValidationException Thrown if the field was not valid
     */
    public function validateField(object $object, ValidationContext $validationContext): void
    {
        $errors = new ErrorCollection();
        $allFieldsAreValid = true;
        $fieldValue = ($this->fieldAccessor)($object);

        foreach ($this->fieldRules as $fieldRule) {
            $fieldIsValid = !$fieldRule->passes($fieldValue, $validationContext);
            $allFieldsAreValid = $allFieldsAreValid && $fieldIsValid;

            if (!$fieldIsValid) {
                // TODO: Probably need to rethink how/where we grab error messages from
                //$errors->add($this->fieldName, $rules->getErrors($this->fieldName));
            }
        }

        if (!$allFieldsAreValid) {
            throw new ValidationException($errors);
        }
    }
}
