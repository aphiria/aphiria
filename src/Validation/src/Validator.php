<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\ErrorMessageInterpolationException;
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
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function tryValidateMethod(object $object, string $methodName, array &$violations = []): bool
    {
        $context = new ValidationContext($object, null, $methodName);
        $successful = $this->tryValidateMethodWithContext($object, $methodName, $context);
        $violations = $context->getConstraintViolations();

        return $successful;
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function tryValidateObject(object $object, array &$violations = []): bool
    {
        $context = new ValidationContext($object);
        $successful = $this->tryValidateObjectWithContext($object, $context);
        $violations = $context->getConstraintViolations();

        return $successful;
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function tryValidateProperty(object $object, string $propertyName, array &$violations = []): bool
    {
        $context = new ValidationContext($object, $propertyName);
        $successful = $this->tryValidatePropertyWithContext($object, $propertyName, $context);
        $violations = $context->getConstraintViolations();

        return $successful;
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function tryValidateValue($value, array $constraints, array &$violations = []): bool
    {
        $context = new ValidationContext($value);
        $successful = $this->tryValidateValueWithContext($value, $constraints, $context);
        $violations = $context->getConstraintViolations();

        return $successful;
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function validateMethod(object $object, string $methodName): void
    {
        $this->validateMethodWithContext($object, $methodName, new ValidationContext($object, null, $methodName));
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function validateObject(object $object): void
    {
        $this->validateObjectWithContext($object, new ValidationContext($object));
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function validateProperty(object $object, string $propertyName): void
    {
        $this->validatePropertyWithContext($object, $propertyName, new ValidationContext($object, $propertyName));
    }

    /**
     * @inheritdoc
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    public function validateValue($value, array $constraints): void
    {
        $this->validateValueWithContext($value, $constraints, new ValidationContext($value));
    }

    /**
     * Tries to validate a method in an object in a context
     *
     * @param object $object The object whose method we're validating
     * @param string $methodName The name of the method to validate
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the method was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function tryValidateMethodWithContext(object $object, string $methodName, ValidationContext $validationContext): bool
    {
        try {
            $this->validateMethodWithContext($object, $methodName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * Tries to validate an object in a context
     *
     * @param object $object The object we're validating
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the method was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function tryValidateObjectWithContext(object $object, ValidationContext $validationContext): bool
    {
        try {
            $this->validateObjectWithContext($object, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * Tries to validate a property in an object in a context
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the method was valid, otherwise false
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function tryValidatePropertyWithContext(object $object, string $propertyName, ValidationContext $validationContext): bool
    {
        try {
            $this->validatePropertyWithContext($object, $propertyName, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * Tries to validate a single value in a context
     *
     * @param mixed $value The value to validate
     * @param IConstraint[] $constraints The list of constraints to use
     * @param ValidationContext $validationContext The context to perform validation in
     * @return bool True if the value was valid, otherwise false
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function tryValidateValueWithContext($value, array $constraints, ValidationContext $validationContext): bool
    {
        try {
            $this->validateValueWithContext($value, $constraints, $validationContext);

            return true;
        } catch (ValidationException $ex) {
            return false;
        }
    }

    /**
     * Validates a method in an object in a context
     *
     * @param object $object The object whose method we're validating
     * @param string $methodName The name of the method to validate
     * @param ValidationContext $validationContext The context to validate in
     * @throws ValidationException Thrown if the method was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws InvalidArgumentException Thrown if the method does not exist
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function validateMethodWithContext(object $object, string $methodName, ValidationContext $validationContext): void
    {
        $class = \get_class($object);

        if (!\method_exists($object, $methodName)) {
            throw new InvalidArgumentException("$class::$methodName() does not exist");
        }

        try {
            $reflectionMethod = new ReflectionMethod($class, $methodName);
            // Cannot test failed reflection calls
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $ex) {
            throw new ValidationException($validationContext->getConstraintViolations(), "Failed to reflect method $class::$methodName()", 0, $ex);
            // @codeCoverageIgnoreEnd
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
            $allConstraintsPassed = $allConstraintsPassed && $this->tryValidateObjectWithContext($methodValue, $methodValueValidationContext);
        }

        if (($objectConstraints = $this->objectConstraints->getConstraintsForClass($class)) !== null) {
            foreach ($objectConstraints->getMethodConstraints($methodName) as $constraint) {
                $thisConstraintPassed = $constraint->passes($methodValue);
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
            throw new ValidationException($validationContext->getConstraintViolations(), "Failed to validate $class::$methodName()");
        }
    }

    /**
     * Validates an object in a context
     *
     * @param object $object The object to validate
     * @param ValidationContext $validationContext The context to validate in
     * @throws ValidationException Thrown if the input object was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function validateObjectWithContext(object $object, ValidationContext $validationContext): void
    {
        $allConstraintsPassed = true;
        $reflectionObject = new ReflectionObject($object);
        $reflectionProperties = $reflectionObject->getProperties();
        $reflectionMethods = $reflectionObject->getMethods();

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyValidationContext = new ValidationContext($object, $propertyName, null, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed
                && $this->tryValidatePropertyWithContext($object, $propertyName, $propertyValidationContext);
        }

        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $methodValidationContext = new ValidationContext($object, null, $methodName, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed
                && $this->tryValidateMethodWithContext($object, $methodName, $methodValidationContext);
        }

        if (!$allConstraintsPassed) {
            throw new ValidationException($validationContext->getConstraintViolations(), 'Failed to validate ' . \get_class($object));
        }
    }

    /**
     * Validates a property in an object in a context
     *
     * @param object $object The object whose property we're validating
     * @param string $propertyName The name of the property to validate
     * @param ValidationContext $validationContext The context to validate in
     * @throws ValidationException Thrown if the property was invalid
     * @throws CircularDependencyException Thrown if a circular dependency is detected
     * @throws InvalidArgumentException Thrown if the property does not exist
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function validatePropertyWithContext(object $object, string $propertyName, ValidationContext $validationContext): void
    {
        $class = \get_class($object);

        if (!\property_exists($object, $propertyName)) {
            throw new InvalidArgumentException("$class::$propertyName does not exist");
        }

        try {
            $reflectionProperty = new ReflectionProperty($class, $propertyName);
            // Cannot test failed reflection calls
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $ex) {
            throw new ValidationException($validationContext->getConstraintViolations(), "Failed to reflect property $class::$propertyName", 0, $ex);
            // @codeCoverageIgnoreEnd
        }

        $reflectionProperty->setAccessible(true);
        $propertyValue = $reflectionProperty->getValue($object);
        $allConstraintsPassed = true;

        // Recursively validate the value if it's an object
        if (\is_object($propertyValue)) {
            // Since we're validating a whole new object, null out the property name param
            $propertyValueValidationContext = new ValidationContext($propertyValue, null, null, $validationContext);
            $allConstraintsPassed = $allConstraintsPassed && $this->tryValidateObjectWithContext($propertyValue, $propertyValueValidationContext);
        }

        if (($objectConstraints = $this->objectConstraints->getConstraintsForClass($class)) !== null) {
            foreach ($objectConstraints->getPropertyConstraints($propertyName) as $constraint) {
                $thisConstraintPassed = $constraint->passes($propertyValue);
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
            throw new ValidationException($validationContext->getConstraintViolations(), "Failed to validate $class::$propertyName");
        }
    }

    /**
     * Validates a single value against a list of constraints in a context
     *
     * @param mixed $value The value to validate
     * @param IConstraint[] $constraints The list of constraints to use
     * @param ValidationContext $validationContext The context to validate in
     * @throws ValidationException Thrown if the value was invalid
     * @throws ErrorMessageInterpolationException Thrown if there was an error interpolating the error message
     */
    private function validateValueWithContext($value, array $constraints, ValidationContext $validationContext): void
    {
        $allConstraintsPass = true;

        foreach ($constraints as $constraint) {
            $thisConstraintPassed = $constraint->passes($value);
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
            throw new ValidationException($validationContext->getConstraintViolations());
        }
    }
}
