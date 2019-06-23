<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Encoding;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Defines an object encoder
 */
final class ObjectEncoder implements IEncoder
{
    /** @var EncoderRegistry The encoder registry */
    private EncoderRegistry $encoders;
    /** @var IPropertyNameFormatter|null The property name formatter to use */
    private ?IPropertyNameFormatter $propertyNameFormatter;
    /** @var array The mapping of types to encoded property names to ignore */
    private array $ignoredEncodedPropertyNamesByType = [];

    /**
     * @param EncoderRegistry $encoders The encoder registry
     * @param IPropertyNameFormatter|null $propertyNameFormatter The property name formatter to use
     */
    public function __construct(EncoderRegistry $encoders, IPropertyNameFormatter $propertyNameFormatter = null)
    {
        $this->encoders = $encoders;
        $this->propertyNameFormatter = $propertyNameFormatter;
    }

    /**
     * Adds a property to ignore during encoding
     *
     * @param string $type The type whose property we're ignoring
     * @param string|string[] $propertyNames The name or list of names of the properties to ignore
     * @throws InvalidArgumentException Thrown if the property names were not a string or array
     */
    public function addIgnoredProperty(string $type, $propertyNames): void
    {
        if (!is_string($propertyNames) && !is_array($propertyNames)) {
            throw new InvalidArgumentException('Property name must be a string or array of strings');
        }

        if (!isset($this->ignoredEncodedPropertyNamesByType[$type])) {
            $this->ignoredEncodedPropertyNamesByType[$type] = [];
        }

        foreach ((array)$propertyNames as $propertyName) {
            $this->ignoredEncodedPropertyNamesByType[$type][$this->normalizePropertyName($propertyName)] = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function decode($objectHash, string $type, EncodingContext $context)
    {
        if (!class_exists($type)) {
            throw new InvalidArgumentException("Type $type is not a valid class name");
        }

        if (!is_array($objectHash)) {
            throw new InvalidArgumentException('Value must be an associative array');
        }

        try {
            $reflectionClass = new ReflectionClass($type);
        } catch (ReflectionException $ex) {
            throw new EncodingException("Reflection failed on type $type", 0, $ex);
        }

        $normalizedPropertyNames = $this->normalizeHashProperties($objectHash);
        $unusedNormalizedPropertyNames = $normalizedPropertyNames;
        $constructorParams = [];
        $constructor = $reflectionClass->getConstructor();

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $constructorParam) {
                $encodedConstructorParamName = $this->normalizePropertyName($constructorParam->getName());

                if (isset($normalizedPropertyNames[$encodedConstructorParamName])) {
                    $constructorParamValue = $objectHash[$normalizedPropertyNames[$encodedConstructorParamName]];

                    try {
                        $decodedConstructorParamValue = $this->decodeConstructorParamValue(
                            $constructorParam,
                            $constructorParamValue,
                            $reflectionClass,
                            $encodedConstructorParamName,
                            $context
                        );
                    } catch (ReflectionException $ex) {
                        throw new EncodingException('Failed to decode constructor param ' . $constructorParam->getName(), 0, $ex);
                    }

                    if ($constructorParam->isVariadic()) {
                        $constructorParams = array_merge($constructorParams, $decodedConstructorParamValue);
                    } else {
                        $constructorParams[] = $decodedConstructorParamValue;
                    }

                    unset($unusedNormalizedPropertyNames[$encodedConstructorParamName]);
                } elseif ($constructorParam->isDefaultValueAvailable()) {
                    $constructorParams[] = $constructorParam->getDefaultValue();
                } elseif ($constructorParam->allowsNull()) {
                    // The property wasn't in the hash, but the parameter is nullable
                    $constructorParams[] = null;
                } else {
                    throw new EncodingException("No value specified for parameter \"{$constructorParam->getName()}\"");
                }
            }
        }

        $object = $reflectionClass->newInstanceArgs($constructorParams);
        $defaultPropertyValues = $reflectionClass->getDefaultProperties();

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $publicProperty) {
            $publicPropertyName = $publicProperty->getName();

            // If this property already has a non-default value set, don't reset it
            if (
                isset(
                    $object->{$publicPropertyName},
                    $defaultPropertyValues[$publicPropertyName]
                ) && $object->{$publicPropertyName} !== $defaultPropertyValues[$publicPropertyName]
            ) {
                continue;
            }

            $encodedPropertyName = $this->normalizePropertyName($publicPropertyName);

            if (isset($unusedNormalizedPropertyNames[$encodedPropertyName])) {
                $encodedPropertyValue = $objectHash[$normalizedPropertyNames[$encodedPropertyName]];
                $object->{$publicPropertyName} = $this->decodePropertyValue(
                    $publicProperty,
                    $encodedPropertyValue,
                    $reflectionClass,
                    $encodedPropertyName,
                    $context
                );
                unset($unusedNormalizedPropertyNames[$encodedPropertyName]);
            }
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function encode($object, EncodingContext $context)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Value must be an object');
        }

        if ($context->isCircularReference($object)) {
            throw new EncodingException('Circular reference detected');
        }

        $encodedObject = [];
        $reflectionObject = new ReflectionObject($object);

        foreach ($reflectionObject->getProperties() as $property) {
            if ($this->propertyIsIgnored($reflectionObject->getName(), $property->getName())) {
                continue;
            }

            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            $formattedPropertyName = $this->propertyNameFormatter === null ?
                $property->getName() :
                $this->propertyNameFormatter->formatPropertyName($property->getName());
            $propertyValue = $property->getValue($object);
            $encodedObject[$formattedPropertyName] = $this->encoders->getEncoderForValue($propertyValue)
                ->encode($propertyValue, $context);
        }

        return $encodedObject;
    }

    /**
     * Decodes an array value
     *
     * @param ReflectionParameter|ReflectionProperty $reflection The reflection of the property/param to decode
     * @param mixed $encodedValue The encoded value
     * @param EncodingContext $context The encoding context
     * @return mixed The decoded value
     * @throws InvalidArgumentException Thrown if the input reflection parameter is not the correct type
     * @throws EncodingException Thrown if the value was not an array
     */
    protected function decodeArrayValue(
        $reflection,
        $encodedValue,
        EncodingContext $context
    ) {
        if (!$reflection instanceof ReflectionParameter && !$reflection instanceof  ReflectionProperty) {
            throw new InvalidArgumentException('Must pass in an instance of ' . ReflectionParameter::class . ' or ' . ReflectionProperty::class);
        }

        if (!is_array($encodedValue)) {
            throw new EncodingException('Value must be an array');
        }

        if (count($encodedValue) === 0) {
            return [];
        }

        // Parameters can be variadic, properties cannot
        if ($reflection instanceof ReflectionParameter && $reflection->isVariadic() && $reflection->hasType()) {
            $type = $reflection->getType()->getName() . '[]';

            return $this->encoders->getEncoderForType($type)
                ->decode($encodedValue, $type, $context);
        }

        if (is_object($encodedValue[0])) {
            $type = get_class($encodedValue[0]) . '[]';

            return $this->encoders->getEncoderForType($type)
                ->decode($encodedValue, $type, $context);
        }

        $type = gettype($encodedValue[0]) . '[]';

        return $this->encoders->getEncoderForType($type)
            ->decode($encodedValue, $type, $context);
    }

    /**
     * Decodes a constructor parameter value
     *
     * @param ReflectionParameter $constructorParam The constructor parameter to decode
     * @param mixed $constructorParamValue The encoded constructor parameter value
     * @param ReflectionClass $reflectionClass The reflection class we're trying to instantiate
     * @param string $normalizedHashPropertyName The encoded property name from the hash
     * @param EncodingContext $context The encoding context
     * @return mixed The decoded constructor parameter value
     * @throws EncodingException Thrown if the value could not be automatically decoded
     * @throws ReflectionException Thrown if there was an error reflecting
     */
    private function decodeConstructorParamValue(
        ReflectionParameter $constructorParam,
        $constructorParamValue,
        ReflectionClass $reflectionClass,
        string $normalizedHashPropertyName,
        EncodingContext $context
    ) {
        if ($constructorParam->hasType() && !$constructorParam->isArray() && !$constructorParam->isVariadic()) {
            $constructorParamType = $constructorParam->getType()->getName();

            return $this->encoders->getEncoderForType($constructorParamType)
                ->decode($constructorParamValue, $constructorParamType, $context);
        }

        if ($constructorParam->isVariadic() || $constructorParam->isArray()) {
            return $this->decodeArrayValue(
                $constructorParam,
                $constructorParamValue,
                $context
            );
        }

        // Check for a type from a matching property
        if (
            $reflectionClass->hasProperty($constructorParam->getName())
            && ($property = $reflectionClass->getProperty($constructorParam->getName()))
            && $property->hasType()
        ) {
            $propertyType = $property->getType()->getName();

            return $this->encoders->getEncoderForType($propertyType)
                ->decode($constructorParamValue, $propertyType, $context);
        }

        // Check for a type from a getter
        $decodedValue = null;

        if (
            $this->tryDecodeValueFromGetterType(
                $reflectionClass,
                $normalizedHashPropertyName,
                $constructorParamValue,
                $context,
                $decodedValue
            )
        ) {
            return $decodedValue;
        }

        // At this point, let's just check if the value we're trying to decode is a scalar, and if so, just return it
        if (is_scalar($constructorParamValue)) {
            $type = gettype($constructorParamValue);

            return $this->encoders->getEncoderForType($type)
                ->decode($constructorParamValue, $type, $context);
        }

        throw new EncodingException("Failed to decode constructor parameter {$constructorParam->getName()}");
    }

    /**
     * Decodes a property value
     *
     * @param ReflectionProperty $property The property to decode
     * @param mixed $encodedPropertyValue The encoded property value
     * @param ReflectionClass $reflectionClass The reflection class we're trying to instantiate
     * @param string $normalizedHashPropertyName The encoded property name from the hash
     * @param EncodingContext $context The encoding context
     * @return mixed The decoded property value
     * @throws EncodingException Thrown if the value could not be automatically decoded
     */
    private function decodePropertyValue(
        ReflectionProperty $property,
        $encodedPropertyValue,
        ReflectionClass $reflectionClass,
        string $normalizedHashPropertyName,
        EncodingContext $context
    ) {
        $propertyType = $property->hasType() ? $property->getType()->getName() : null;

        if ($propertyType === 'array') {
            return $this->decodeArrayValue(
                $property,
                $encodedPropertyValue,
                $context
            );
        }

        if ($propertyType !== null) {
            return $this->encoders->getEncoderForType($propertyType)
                ->decode($encodedPropertyValue, $propertyType, $context);
        }

        $decodedValue = null;

        if (
            $this->tryDecodeValueFromGetterType(
                $reflectionClass,
                $normalizedHashPropertyName,
                $encodedPropertyValue,
                $context,
                $decodedValue
            )
        ) {
            return $decodedValue;
        }

        // At this point, let's just check if the value we're trying to decode is a scalar, and if so, just return it
        if (is_scalar($encodedPropertyValue)) {
            $type = gettype($encodedPropertyValue);

            return $this->encoders->getEncoderForType($type)
                ->decode($encodedPropertyValue, $type, $context);
        }

        throw new EncodingException("Failed to decode property {$encodedPropertyValue->getName()}");
    }

    /**
     * Gets the normalized hash property names to original names
     *
     * @param array $objectHash The object hash whose properties we're normalizing
     * @return array The mapping of normalized names to original names
     */
    private function normalizeHashProperties(array $objectHash): array
    {
        $encodedHashProperties = [];

        foreach ($objectHash as $propertyName => $propertyValue) {
            $encodedHashProperties[$this->normalizePropertyName($propertyName)] = $propertyName;
        }

        return $encodedHashProperties;
    }

    /**
     * Normalizes a property name to support fuzzy matching
     *
     * @param string $propertyName The property name to normalize
     * @return string The normalized property name
     */
    private function normalizePropertyName(string $propertyName): string
    {
        return strtolower(str_replace('_', '', $propertyName));
    }

    /**
     * Checks whether or not a property on a type is ignored
     *
     * @param string $type The type to check
     * @param string $propertyName The property name to check
     * @return bool True if the property should be ignored, otherwise false
     */
    private function propertyIsIgnored(string $type, string $propertyName): bool
    {
        return isset($this->ignoredEncodedPropertyNamesByType[$type][$this->normalizePropertyName($propertyName)]);
    }

    /**
     * Decodes a value using the type info from get, is, or has methods
     *
     * @param ReflectionClass $reflectionClass The reflection class
     * @param string $normalizedPropertyName The normalized property name
     * @param mixed $encodedValue The encoded value
     * @param EncodingContext $context The encoding context
     * @param mixed The decoded value
     * @return bool Returns true if the value was successfully decoded, otherwise false
     */
    private function tryDecodeValueFromGetterType(
        ReflectionClass $reflectionClass,
        string $normalizedPropertyName,
        $encodedValue,
        EncodingContext $context,
        &$decodedValue
    ): bool {
        // Check if we can infer the type from any getters or setters
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (
                !$reflectionMethod->hasReturnType()
                || $reflectionMethod->getReturnType() === 'array'
                || $reflectionMethod->isConstructor()
                || $reflectionMethod->isDestructor()
                || $reflectionMethod->getNumberOfRequiredParameters() > 0
            ) {
                continue;
            }

            $propertyName = null;

            // Try to extract the property name from the getter/has-er/is-er
            if (strpos($reflectionMethod->name, 'get') === 0 || strpos($reflectionMethod->name, 'has') === 0) {
                $propertyName = lcfirst(substr($reflectionMethod->name, 3));
            } elseif (strpos($reflectionMethod->name, 'is') === 0) {
                $propertyName = lcfirst(substr($reflectionMethod->name, 2));
            }

            if ($propertyName === null) {
                continue;
            }

            $encodedPropertyName = $this->normalizePropertyName($propertyName);

            // This getter matches the property name we're looking for
            if ($encodedPropertyName === $normalizedPropertyName) {
                try {
                    $reflectionMethodReturnType = $reflectionMethod->getReturnType()->getName();
                    $decodedValue = $this->encoders->getEncoderForType($reflectionMethodReturnType)
                        ->decode($encodedValue, $reflectionMethodReturnType, $context);

                    return true;
                } catch (EncodingException $ex) {
                    return false;
                }
            }
        }

        return false;
    }
}
