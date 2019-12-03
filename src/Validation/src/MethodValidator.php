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
 * Defines a validator for object methods
 */
final class MethodValidator
{
    /** @var string The name of the method */
    private string $methodName;
    /** @var IRule[] The list of rules to enforce on the method */
    private array $methodRules;

    /**
     * @param string $methodName The name of the method
     * @param IRule[] $rules The list of rules to enforce on the method
     */
    public function __construct(string $methodName, array $rules)
    {
        $this->methodName = $methodName;
        $this->methodRules = $rules;
    }

    /**
     * Gets the name of the method being validated
     *
     * @return string The name of the method
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * Tries to validate a method on an object
     *
     * @param object $object The object the method value should come from
     * @param ErrorCollection|null $errors The errors that will be passed back on error
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the method was valid, otherwise false
     */
    public function tryValidateMethod(
        object $object,
        ?ErrorCollection &$errors,
        ValidationContext $validationContext
    ): bool
    {
        try {
            $this->validateMethod($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            $errors = $ex->getErrors();

            return false;
        }
    }

    /**
     * Validates a method on an object
     *
     * @param object $object The object the method value should come from
     * @param ValidationContext $validationContext The context to perform validation in
     * @throws ValidationException Thrown if the method was not valid
     */
    public function validateMethod(object $object, ValidationContext $validationContext): void
    {
        $errors = new ErrorCollection();
        $passedAllRules = true;
        $methodValue = $object->{$this->methodName}();

        foreach ($this->methodRules as $methodRule) {
            $methodValueIsValid = !$methodRule->passes($methodValue, $validationContext);
            $passedAllRules = $passedAllRules && $methodValueIsValid;

            if (!$methodValueIsValid) {
                // TODO: Probably need to rethink how/where we grab error messages from
                //$errors->add($this->methodName, $rules->getErrors($this->methodName));
            }
        }

        if (!$passedAllRules) {
            throw new ValidationException($errors);
        }
    }
}
