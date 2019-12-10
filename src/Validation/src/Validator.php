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
    /** @var string[] The list of magic methods to explicitly ignore */
    private static array $magicMethods = [
        '__call' => true,
        '__callStatic' => true,
        '__clone' => true,
        '__construct' => true,
        '__destruct' => true,
        '__get' => true,
        '__invoke' => true,
        '__isset' => true,
        '__set' => true,
        '__set_state' => true,
        '__sleep' => true,
        '__toString' => true,
        '__unset' => true,
        '__wakeup' => true
    ];
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
    public function tryValidateMethod(object $object, string $methodName, ValidationContext $validationContext): bool
    {
        try {
            $this->validateMethod($object, $methodName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateObject(object $object, ValidationContext $validationContext): bool
    {
        try {
            $this->validateObject($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateProperty(object $object, string $propertyName, ValidationContext $validationContext): bool
    {
        try {
            $this->validateProperty($object, $propertyName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function tryValidateValue($value, array $rules, ValidationContext $validationContext): bool
    {
        try {
            $this->validateValue($value, $rules, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateMethod(object $object, string $methodName, ValidationContext $validationContext): void
    {
        $class = \get_class($object);

        if (!\method_exists($object, $methodName)) {
            throw new InvalidArgumentException("$class::$methodName() does not exist");
        }

        $reflectionMethod = new ReflectionMethod($class, $methodName);

        // Don't bother with magic methods or methods that require parameters
        if (
            $reflectionMethod->getNumberOfRequiredParameters() > 0
            || isset(self::$magicMethods[$reflectionMethod->getName()])
        ) {
            return;
        }

        $reflectionMethod->setAccessible(true);
        $methodValue = $reflectionMethod->invoke($object);
        $allRulesPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($methodValue)) {
            // Since we're validating a whole new object, null out the method name param
            $methodValidationContext = new ValidationContext($methodValue, null, null, $validationContext);
            $allRulesPassed = $allRulesPassed && $this->tryValidateObject($methodValue, $methodValidationContext);
        }

        foreach ($this->rules->getMethodRules($class, $methodName) as $rule) {
            $thisRulePassed = $rule->passes($methodValue, $validationContext);
            $allRulesPassed = $allRulesPassed && $thisRulePassed;

            if (!$thisRulePassed) {
                $validationContext->addRuleViolation(new RuleViolation(
                    $rule,
                    $methodValue,
                    $validationContext->getRootValue(),
                    null,
                    $methodName
                ));
            }
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext);
        }
    }

    /**
     * @inheritdoc
     */
    public function validateObject(object $object, ValidationContext $validationContext): void
    {
        $allRulesPassed = true;
        $reflectionObject = new \ReflectionObject($object);
        $reflectionProperties = $reflectionObject->getProperties();
        $reflectionMethods = $reflectionObject->getMethods();

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyValidationContext = new ValidationContext($object, $propertyName, null, $validationContext);
            $allRulesPassed = $allRulesPassed
                && $this->tryValidateProperty($object, $propertyName, $propertyValidationContext);
        }

        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $methodValidationContext = new ValidationContext($object, null, $methodName, $validationContext);
            $allRulesPassed = $allRulesPassed
                && $this->tryValidateMethod($object, $methodName, $methodValidationContext);
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext);
        }
    }

    /**
     * @inheritdoc
     */
    public function validateProperty(object $object, string $propertyName, ValidationContext $validationContext): void
    {
        $class = \get_class($object);

        if (!\property_exists($object, $propertyName)) {
            throw new InvalidArgumentException("$class::$propertyName does not exist");
        }

        $reflectionProperty = new ReflectionProperty($class, $propertyName);
        $reflectionProperty->setAccessible(true);
        $propertyValue = $reflectionProperty->getValue($object);
        $allRulesPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($propertyValue)) {
            // Since we're validating a whole new object, null out the property name param
            $propertyValidationContext = new ValidationContext($propertyValue, null, null, $validationContext);
            $allRulesPassed = $allRulesPassed && $this->tryValidateObject($propertyValue, $propertyValidationContext);
        }

        foreach ($this->rules->getPropertyRules($class, $propertyName) as $rule) {
            $thisRulePassed = $rule->passes($propertyValue, $validationContext);
            $allRulesPassed = $allRulesPassed && $thisRulePassed;

            if (!$thisRulePassed) {
                $validationContext->addRuleViolation(new RuleViolation(
                    $rule,
                    $propertyValue,
                    $validationContext->getRootValue(),
                    $propertyName
                ));
            }
        }

        if (!$allRulesPassed) {
            throw new ValidationException($validationContext);
        }
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value, array $rules, ValidationContext $validationContext): void
    {
        $allRulesPass = true;

        foreach ($rules as $rule) {
            $thisRulePassed = $rule->passes($value, $validationContext);
            $allRulesPass = $allRulesPass && $thisRulePassed;

            if (!$thisRulePassed) {
                $validationContext->addRuleViolation(new RuleViolation(
                    $rule,
                    $value,
                    $value
                ));
            }
        }

        if (!$allRulesPass) {
            throw new ValidationException($validationContext);
        }
    }
}
