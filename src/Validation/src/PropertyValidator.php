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
 * Defines a validator for an individual property
 */
final class PropertyValidator
{
    /** @var string The name of the property  */
    private string $propertyName;
    /** @var IRule[] The list of rules to enforce on the property */
    private array $propertyRules;

    /**
     * @param string $propertyName The name of the property
     * @param IRule[] $rules The list of rules to enforce on the property
     */
    public function __construct(string $propertyName, array $rules)
    {
        $this->propertyName = $propertyName;
        $this->propertyRules = $rules;
    }

    /**
     * Gets the name of the property being validated
     *
     * @return string The name of the property
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * Tries to validate a property on an object
     *
     * @param object $object The object the property value should come from
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the property was valid, otherwise false
     */
    public function tryValidateProperty(
        object $object,
        ?ErrorCollection &$errors,
        ValidationContext $validationContext
    ): bool
    {
        try {
            $this->validateProperty($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

    /**
     * Validates a property on an object
     *
     * @param object $object The object the property value should come from
     * @param ValidationContext $validationContext The context to perform validation in
     * @throws ValidationException Thrown if the property was not valid
     */
    public function validateProperty(object $object, ValidationContext $validationContext): void
    {
        $errors = new ErrorCollection();
        $passedAllRules = true;
        $propertyValue = $object->{$this->propertyName};

        foreach ($this->propertyRules as $propertyRule) {
            $propertyValueIsValid = !$propertyRule->passes($propertyValue, $validationContext);
            $passedAllRules = $passedAllRules && $propertyValueIsValid;

            if (!$propertyValueIsValid) {
                // TODO: Probably need to rethink how/where we grab error messages from
                //$errors->add($this->propertyName, $rules->getErrors($this->propertyName));
            }
        }

        if (!$passedAllRules) {
            throw new ValidationException($errors);
        }
    }
}
