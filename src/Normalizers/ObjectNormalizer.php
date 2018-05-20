<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Normalizers;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;

/**
 * Defines an object normalizer
 */
class ObjectNormalizer implements INormalizer
{
    /** @var INormalizer The parent normalizer */
    private $parentNormalizer;

    /**
     * @param INormalizer $parentNormalizer The parent normalizer
     */
    public function __construct(INormalizer $parentNormalizer)
    {
        $this->parentNormalizer = $parentNormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($objectHash, string $type)
    {
        if (!\class_exists($type)) {
            throw new InvalidArgumentException("Type $type is not a valid class name");
        }

        if (!\is_array($objectHash)) {
            throw new InvalidArgumentException('Value must be an associative array');
        }

        $reflectionClass = new ReflectionClass($type);
        $normalizedHashPropertyNames = $this->normalizeHashProperties($objectHash);
        $constructorParams = [];
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return $reflectionClass->newInstance();
        }

        foreach ($constructor->getParameters() as $constructorParam) {
            $normalizedConstructorParamName = $this->normalizePropertyName($constructorParam->getName());

            if (isset($normalizedHashPropertyNames[$normalizedConstructorParamName])) {
                $constructorParamValue = $objectHash[$normalizedHashPropertyNames[$normalizedConstructorParamName]];
                $denormalizedConstructorParamValue = $this->denormalizeConstructorParamValue(
                    $constructorParam,
                    $constructorParamValue,
                    $reflectionClass,
                    $normalizedConstructorParamName
                );

                if ($constructorParam->isVariadic()) {
                    $constructorParams = array_merge($constructorParams, $denormalizedConstructorParamValue);
                } else {
                    $constructorParams[] = $denormalizedConstructorParamValue;
                }
            } elseif ($constructorParam->isDefaultValueAvailable()) {
                $constructorParams[] = $constructorParam->getDefaultValue();
            } elseif ($constructorParam->allowsNull()) {
                // The property wasn't in the hash, but the parameter is nullable
                $constructorParams[] = null;
            } else {
                throw new NormalizationException("No value specified for parameter \"{$constructorParam->getName()}\"");
            }
        }

        return $reflectionClass->newInstanceArgs($constructorParams);
    }

    /**
     * Denormalizes a constructor parameter value
     *
     * @param ReflectionParameter $constructorParam The constructor parameter to denormalize
     * @param mixed $constructorParamValue The normalized construtctor parameter value
     * @param ReflectionClass $reflectionClass The reflection class we're trying to instantiate
     * @param string $normalizedHashPropertyName The normalized property name from the hash
     * @return mixed The denormalized constructor parameter value
     * @throws NormalizationException Thrown if the value could not be automatically denormalized
     */
    protected function denormalizeConstructorParamValue(
        ReflectionParameter $constructorParam,
        $constructorParamValue,
        ReflectionClass $reflectionClass,
        string $normalizedHashPropertyName
    ) {
        if ($constructorParam->hasType() && !$constructorParam->isArray() && !$constructorParam->isVariadic()) {
            return $this->parentNormalizer->denormalize($constructorParamValue, $constructorParam->getType());
        }

        if ($constructorParam->isVariadic() || $constructorParam->isArray()) {
            if (!\is_array($constructorParamValue)) {
                throw new NormalizationException('Value must be an array');
            }

            if (\count($constructorParamValue) === 0) {
                return [];
            }

            if ($constructorParam->isVariadic() && $constructorParam->hasType()) {
                return $this->parentNormalizer->denormalize($constructorParamValue, $constructorParam->getType() . '[]');
            }

            if (\is_object($constructorParamValue[0])) {
                return $this->parentNormalizer->denormalize(
                    $constructorParamValue,
                    \get_class($constructorParamValue[0]) . '[]'
                );
            }

            return $this->parentNormalizer->denormalize(
                $constructorParamValue,
                gettype($constructorParamValue[0]) . '[]'
            );
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

            $normalizedPropertyName = $this->normalizePropertyName($propertyName);

            // This getter matches the property name we're looking for
            if ($normalizedPropertyName === $normalizedHashPropertyName) {
                return $this->parentNormalizer->denormalize($constructorParamValue, $reflectionMethod->getReturnType());
            }
        }

        // At this point, let's just check if the value we're trying to denormalize is a scalar, and if so, just return it
        if (\is_scalar($constructorParamValue)) {
            return $this->parentNormalizer->denormalize($constructorParamValue, \gettype($constructorParamValue));
        }

        throw new NormalizationException("Failed to denormalize constructor parameter {$constructorParam->getName()}");
    }

    /**
     * @inheritdoc
     */
    public function normalize($object, array $interceptors = [])
    {
        if (!\is_object($object)) {
            throw new InvalidArgumentException('Value must be an object');
        }

        $normalizedObject = [];
        $reflectionObject = new ReflectionObject($object);

        foreach ($reflectionObject->getProperties() as $property) {
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            $normalizedObject[$property->getName()] = $this->parentNormalizer->normalize(
                $property->getValue($object),
                $interceptors
            );
        }

        foreach ($interceptors as $interceptor) {
            $normalizedObject = $interceptor->onPostNormalization($normalizedObject);
        }

        return $normalizedObject;
    }

    /**
     * Gets the normalized hash property names to original names
     *
     * @param array $objectHash The object hash whose properties we're normalizing
     * @return array The mapping of normalized names to original names
     */
    protected function normalizeHashProperties(array $objectHash): array
    {
        $normalizedHashProperties = [];

        foreach ($objectHash as $propertyName => $propertyValue) {
            $normalizedHashProperties[$this->normalizePropertyName($propertyName)] = $propertyName;
        }

        return $normalizedHashProperties;
    }

    /**
     * Normalizes a property name to support fuzzy matching
     *
     * @param string $propertyName The property name to normalize
     * @return string The normalized property name
     */
    protected function normalizePropertyName(string $propertyName): string
    {
        return strtolower(str_replace('_', '', $propertyName));
    }
}
