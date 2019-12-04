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

use InvalidArgumentException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Defines the validator
 */
final class Validator implements IValidator
{
    /** @var RuleRegistry The registry of rules */
    private RuleRegistry $rules;

    /**
     * @param RuleRegistry $rules The registry of rules
     */
    public function __construct(RuleRegistry $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @inheritdoc
     */
    public function tryValidateMethod(
        object $object,
        string $methodName,
        ValidationContext &$validationContext = null
    ): bool {
        $validationContext ??= new ValidationContext($object);

        try {
            $this->validateMethod($object, $methodName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            self::setErrorsFromException($validationContext, $ex);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateObject(
        object $object,
        ValidationContext &$validationContext = null
    ): bool {
        $validationContext ??= new ValidationContext($object);

        try {
            $this->validateObject($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            self::setErrorsFromException($validationContext, $ex);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateProperty(
        object $object,
        string $propertyName,
        ValidationContext &$validationContext = null
    ): bool {
        $validationContext ??= new ValidationContext($object);

        try {
            $this->validateProperty($object, $propertyName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            self::setErrorsFromException($validationContext, $ex);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateValue(
        $value,
        array $rules,
        ValidationContext &$validationContext = null
    ): bool {
        $validationContext ??= new ValidationContext($value);

        try {
            $this->validateValue($value, $rules, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            self::setErrorsFromException($validationContext, $ex);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateMethod(object $object, string $methodName, ValidationContext &$validationContext = null): void
    {
        $class = \get_class($object);

        if (!\method_exists($object, $methodName)) {
            throw new InvalidArgumentException("$class::$methodName() does not exist");
        }

        $validationContext ??= new ValidationContext($object);
        $reflectionMethod = new ReflectionMethod($class, $methodName);
        $reflectionMethod->setAccessible(true);
        $methodValue = $reflectionMethod->invoke($object);
        $allRulesPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($methodValue)) {
            // TODO: How can I use the context to keep track of this object + previous one so I can detect a circular dependency?  Do I need the concept of an object stack?  Where would I push objects onto the stack?
            $allRulesPassed = $allRulesPassed && $this->tryValidateObject($methodValue, $validationContext);
        }

        foreach ($this->rules->getMethodRules($class, $methodName) as $rule) {
            $thisRulePassed = $rule->passes($methodValue, $validationContext);
            $allRulesPassed = $allRulesPassed && $thisRulePassed;

            if (!$thisRulePassed) {
                // TODO: How do I grab these error messages?
                $validationContext->getErrors()->add('', '');
            }
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext->getErrors());
        }
    }

    /**
     * @inheritdoc
     */
    public function validateObject(object $object, ValidationContext &$validationContext = null): void
    {
        $validationContext ??= new ValidationContext($object);
        $allRulesPassed = true;
        $reflectionObject = new \ReflectionObject($object);
        $reflectionProperties = $reflectionObject->getProperties();
        $reflectionMethods = $reflectionObject->getMethods();

        foreach ($reflectionProperties as $reflectionProperty) {
            $allRulesPassed = $allRulesPassed
                && $this->tryValidateProperty($object, $reflectionProperty->getName(), $validationContext);
        }

        foreach ($reflectionMethods as $reflectionMethod) {
            $allRulesPassed = $allRulesPassed
                && $this->tryValidateMethod($object, $reflectionMethod->getName(), $validationContext);
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext->getErrors());
        }
    }

    /**
     * @inheritdoc
     */
    public function validateProperty(object $object, string $propertyName, ValidationContext &$validationContext = null): void
    {
        $class = \get_class($object);

        if (!\property_exists($object, $propertyName)) {
            throw new InvalidArgumentException("$class::$propertyName does not exist");
        }

        $validationContext ??= new ValidationContext($object);
        $reflectionProperty = new ReflectionProperty($class, $propertyName);
        $reflectionProperty->setAccessible(true);
        $propertyValue = $reflectionProperty->getValue($object);
        $allRulesPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($propertyValue)) {
            // TODO: How can I use the context to keep track of this object + previous one so I can detect a circular dependency?  Do I need the concept of an object stack?  Where would I push objects onto the stack?
            $allRulesPassed = $allRulesPassed && $this->tryValidateObject($propertyValue, $validationContext);
        }

        foreach ($this->rules->getPropertyRules($class, $propertyName) as $rule) {
            $thisRulePassed = $rule->passes($propertyValue, $validationContext);
            $allRulesPassed = $allRulesPassed && $thisRulePassed;

            if (!$thisRulePassed) {
                // TODO: How do I grab these error messages?
                $validationContext->getErrors()->add('', '');
            }
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext->getErrors());
        }
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value, array $rules, ValidationContext &$validationContext = null): void
    {
        $validationContext ??= new ValidationContext($value);

        foreach ($rules as $rule) {
            $rule->passes($value, $validationContext);
        }
    }

    /**
     * Sets the error collection from an exception
     *
     * @param ValidationContext $validationContext The context that the validation was performed in
     * @param ValidationException $ex The exception to set the errors from
     */
    private static function setErrorsFromException(ValidationContext $validationContext, ValidationException $ex): void
    {
        foreach ($ex->getErrors()->getAll() as $field => $error) {
            $validationContext->getErrors()->add($field, $error);
        }
    }
}
