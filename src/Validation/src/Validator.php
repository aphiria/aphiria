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

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
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
    /** @var ObjectConstraintsRegistry The registry of object constraints */
    private ObjectConstraintsRegistry $objectConstraints;
    /** @var IErrorMessageInterpolator The interpolator for error messages */
    private IErrorMessageInterpolator $errorMessageInterpolator;

    /**
     * @param ObjectConstraintsRegistry $objectConstraints The registry of object constraints
     * @param IErrorMessageInterpolator|null $errorMessageInterpolator The error message interpolator to use
     */
    public function __construct(
        ObjectConstraintsRegistry $objectConstraints,
        IErrorMessageInterpolator $errorMessageInterpolator = null
    ) {
        $this->objectConstraints = $objectConstraints;
        $this->errorMessageInterpolator = $errorMessageInterpolator ?? new StringReplaceErrorMessageInterpolator();
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
    public function tryValidateValue($value, array $constraints, ValidationContext $validationContext): bool
    {
        try {
            $this->validateValue($value, $constraints, $validationContext);

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

        try {
            $reflectionMethod = new ReflectionMethod($class, $methodName);
        } catch (ReflectionException $ex) {
            throw new ValidationException($validationContext, "Failed to reflect method $class::$methodName()", 0, $ex);
        }

        // Don't bother with magic methods or methods that require parameters
        if (
            $reflectionMethod->getNumberOfRequiredParameters() > 0
            || isset(self::$magicMethods[$reflectionMethod->getName()])
        ) {
            return;
        }

        $reflectionMethod->setAccessible(true);
        $methodValue = $reflectionMethod->invoke($object);
        $allConstraintsPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($methodValue)) {
            // Since we're validating a whole new object, null out the method name param
            $methodValueValidationContext = new ValidationContext($methodValue, null, null, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed && $this->tryValidateObject($methodValue, $methodValueValidationContext);
        }

        if (($objectConstraints = $this->objectConstraints->getConstraintsForClass($class)) !== null) {
            foreach ($objectConstraints->getMethodConstraints($methodName) as $constraint) {
                $thisConstraintPassed = $constraint->passes($methodValue, $validationContext);
                $allConstraintsPassed = $allConstraintsPassed && $thisConstraintPassed;

                if (!$thisConstraintPassed) {
                    $validationContext->addConstraintViolation(new ConstraintViolation(
                        $this->errorMessageInterpolator->interpolate(
                            $constraint->getErrorMessageId(),
                            $constraint->getErrorMessagePlaceholders($methodValue)
                        ),
                        $constraint,
                        $methodValue,
                        $validationContext->getValue(),
                        null,
                        $methodName
                    ));
                }
            }
        }

        if (!$allConstraintsPassed) {
            throw new ValidationException($validationContext, "Failed to validate $class::$methodName()");
        }
    }

    /**
     * @inheritdoc
     */
    public function validateObject(object $object, ValidationContext $validationContext): void
    {
        $allConstraintsPassed = true;
        $reflectionObject = new ReflectionObject($object);
        $reflectionProperties = $reflectionObject->getProperties();
        $reflectionMethods = $reflectionObject->getMethods();

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyValidationContext = new ValidationContext($object, $propertyName, null, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed
                && $this->tryValidateProperty($object, $propertyName, $propertyValidationContext);
        }

        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $methodValidationContext = new ValidationContext($object, null, $methodName, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed
                && $this->tryValidateMethod($object, $methodName, $methodValidationContext);
        }

        if (!$allConstraintsPassed) {
            throw new ValidationException($validationContext, 'Failed to validate ' . \get_class($object));
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

        try {
            $reflectionProperty = new ReflectionProperty($class, $propertyName);
        } catch (ReflectionException $ex) {
            throw new ValidationException($validationContext, "Failed to reflect property $class::$propertyName", 0, $ex);
        }

        $reflectionProperty->setAccessible(true);
        $propertyValue = $reflectionProperty->getValue($object);
        $allConstraintsPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($propertyValue)) {
            // Since we're validating a whole new object, null out the property name param
            $propertyValueValidationContext = new ValidationContext($propertyValue, null, null, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed && $this->tryValidateObject($propertyValue, $propertyValueValidationContext);
        }

        if (($objectConstraints = $this->objectConstraints->getConstraintsForClass($class)) !== null) {
            foreach ($objectConstraints->getPropertyConstraints($propertyName) as $constraint) {
                $thisConstraintPassed = $constraint->passes($propertyValue, $validationContext);
                $allConstraintsPassed = $allConstraintsPassed && $thisConstraintPassed;

                if (!$thisConstraintPassed) {
                    $validationContext->addConstraintViolation(new ConstraintViolation(
                        $this->errorMessageInterpolator->interpolate(
                            $constraint->getErrorMessageId(),
                            $constraint->getErrorMessagePlaceholders($propertyValue)
                        ),
                        $constraint,
                        $propertyValue,
                        $validationContext->getValue(),
                        $propertyName
                    ));
                }
            }
        }

        if (!$allConstraintsPassed) {
            throw new ValidationException($validationContext, "Failed to validate $class::$propertyName");
        }
    }

    /**
     * @inheritdoc
     */
    public function validateValue($value, array $constraints, ValidationContext $validationContext): void
    {
        $allConstraintsPass = true;

        foreach ($constraints as $constraint) {
            $thisConstraintPassed = $constraint->passes($value, $validationContext);
            $allConstraintsPass = $allConstraintsPass && $thisConstraintPassed;

            if (!$thisConstraintPassed) {
                $validationContext->addConstraintViolation(new ConstraintViolation(
                    $this->errorMessageInterpolator->interpolate(
                        $constraint->getErrorMessageId(),
                        $constraint->getErrorMessagePlaceholders($value)
                    ),
                    $constraint,
                    $value,
                    $value
                ));
            }
        }

        if (!$allConstraintsPass) {
            throw new ValidationException($validationContext);
        }
    }
}
