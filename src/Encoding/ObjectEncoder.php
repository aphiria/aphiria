<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Encoding;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Defines an object encoder
 */
class ObjectEncoder implements IEncoder
{
    /** @var EncoderRegistry The encoder registry */
    private $encoders;
    /** @var IPropertyNameFormatter|null The property name formatter to use */
    private $propertyNameFormatter;
    /** @var array The mapping of types to encoded property names to ignore */
    private $ignoredEncodedPropertyNamesByType = [];

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
     * @param string $propertyName The name of the property to ignore
     */
    public function addIgnoredProperty(string $type, string $propertyName): void
    {
        if (!isset($this->ignoredEncodedPropertyNamesByType[$type])) {
            $this->ignoredEncodedPropertyNamesByType[$type] = [];
        }

        $this->ignoredEncodedPropertyNamesByType[$type][$this->encodePropertyName($propertyName)] = true;
    }

    /**
     * @inheritdoc
     */
    public function decode($objectHash, string $type)
    {
        if (!\class_exists($type)) {
            throw new InvalidArgumentException("Type $type is not a valid class name");
        }

        if (!\is_array($objectHash)) {
            throw new InvalidArgumentException('Value must be an associative array');
        }

        $reflectionClass = new ReflectionClass($type);
        $encodedHashPropertyNames = $this->encodeHashProperties($objectHash);
        $encodedHashPropertyNamesNotUsed = $encodedHashPropertyNames;
        $constructorParams = [];
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            $object = $reflectionClass->newInstance();
        } else {
            foreach ($constructor->getParameters() as $constructorParam) {
                $encodedConstructorParamName = $this->encodePropertyName($constructorParam->getName());

                if (isset($encodedHashPropertyNames[$encodedConstructorParamName])) {
                    $constructorParamValue = $objectHash[$encodedHashPropertyNames[$encodedConstructorParamName]];
                    $decodedConstructorParamValue = $this->decodeConstructorParamValue(
                        $constructorParam,
                        $constructorParamValue,
                        $reflectionClass,
                        $encodedConstructorParamName
                    );

                    if ($constructorParam->isVariadic()) {
                        $constructorParams = array_merge($constructorParams, $decodedConstructorParamValue);
                    } else {
                        $constructorParams[] = $decodedConstructorParamValue;
                    }

                    unset($encodedHashPropertyNamesNotUsed[$encodedConstructorParamName]);
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

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $publicProperty) {
            $encodedPropertyName = $this->encodePropertyName($publicProperty->getName());

            if (isset($encodedHashPropertyNamesNotUsed[$encodedPropertyName])) {
                // Since public properties aren't typed, we cannot decode it automatically.  So, just use the raw value.
                $propertyValue = $objectHash[$encodedHashPropertyNames[$encodedPropertyName]];
                $object->{$publicProperty->getName()} = $propertyValue;
            }
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function encode($object)
    {
        if (!\is_object($object)) {
            throw new InvalidArgumentException('Value must be an object');
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
                ->encode($propertyValue);
        }

        return $encodedObject;
    }

    /**
     * Decodes a constructor parameter value
     *
     * @param ReflectionParameter $constructorParam The constructor parameter to decode
     * @param mixed $constructorParamValue The encoded constructor parameter value
     * @param ReflectionClass $reflectionClass The reflection class we're trying to instantiate
     * @param string $encodedHashPropertyName The encoded property name from the hash
     * @return mixed The decoded constructor parameter value
     * @throws EncodingException Thrown if the value could not be automatically decoded
     */
    protected function decodeConstructorParamValue(
        ReflectionParameter $constructorParam,
        $constructorParamValue,
        ReflectionClass $reflectionClass,
        string $encodedHashPropertyName
    ) {
        if ($constructorParam->hasType() && !$constructorParam->isArray() && !$constructorParam->isVariadic()) {
            return $this->encoders->getEncoderForType($constructorParam->getType())
                ->decode($constructorParamValue, $constructorParam->getType());
        }

        if ($constructorParam->isVariadic() || $constructorParam->isArray()) {
            if (!\is_array($constructorParamValue)) {
                throw new EncodingException('Value must be an array');
            }

            if (\count($constructorParamValue) === 0) {
                return [];
            }

            if ($constructorParam->isVariadic() && $constructorParam->hasType()) {
                $type = $constructorParam->getType() . '[]';

                return $this->encoders->getEncoderForType($type)
                    ->decode($constructorParamValue, $type);
            }

            if (\is_object($constructorParamValue[0])) {
                $type = \get_class($constructorParamValue[0]) . '[]';

                return $this->encoders->getEncoderForType($type)
                    ->decode($constructorParamValue, $type);
            }

            $type = gettype($constructorParamValue[0]) . '[]';

            return $this->encoders->getEncoderForType($type)
                ->decode($constructorParamValue, $type);
        }

        // Check if we can infer the type from any getters or setters
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (
                !$reflectionMethod->hasReturnType() ||
                $reflectionMethod->getReturnType() === 'array' ||
                $reflectionMethod->isConstructor() ||
                $reflectionMethod->isDestructor() ||
                $reflectionMethod->getNumberOfRequiredParameters() > 0
            ) {
                continue;
            }

            $propertyName = null;

            // Try to extract the property name from the getter/has-er/is-er
            if (substr($reflectionMethod->name, 0, 3) === 'get' || substr($reflectionMethod->name, 0, 3) === 'has') {
                $propertyName = lcfirst(substr($reflectionMethod->name, 3));
            } elseif (substr($reflectionMethod->name, 0, 2) === 'is') {
                $propertyName = lcfirst(substr($reflectionMethod->name, 2));
            }

            if ($propertyName === null) {
                continue;
            }

            $encodedPropertyName = $this->encodePropertyName($propertyName);

            // This getter matches the property name we're looking for
            if ($encodedPropertyName === $encodedHashPropertyName) {
                return $this->encoders->getEncoderForType($reflectionMethod->getReturnType())
                    ->decode($constructorParamValue, $reflectionMethod->getReturnType());
            }
        }

        // At this point, let's just check if the value we're trying to decode is a scalar, and if so, just return it
        if (\is_scalar($constructorParamValue)) {
            $type = \gettype($constructorParamValue);

            return $this->encoders->getEncoderForType($type)
                ->decode($constructorParamValue, $type);
        }

        throw new EncodingException("Failed to decode constructor parameter {$constructorParam->getName()}");
    }

    /**
     * Gets the encoded hash property names to original names
     *
     * @param array $objectHash The object hash whose properties we're encoding
     * @return array The mapping of encoded names to original names
     */
    protected function encodeHashProperties(array $objectHash): array
    {
        $encodedHashProperties = [];

        foreach ($objectHash as $propertyName => $propertyValue) {
            $encodedHashProperties[$this->encodePropertyName($propertyName)] = $propertyName;
        }

        return $encodedHashProperties;
    }

    /**
     * Encodes a property name to support fuzzy matching
     *
     * @param string $propertyName The property name to encode
     * @return string The encoded property name
     */
    protected function encodePropertyName(string $propertyName): string
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
    protected function propertyIsIgnored(string $type, string $propertyName): bool
    {
        return isset($this->ignoredEncodedPropertyNamesByType[$type]) &&
            isset($this->ignoredEncodedPropertyNamesByType[$type][$this->encodePropertyName($propertyName)]);
    }
}
