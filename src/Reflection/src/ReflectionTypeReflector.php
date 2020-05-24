<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionType;

/**
 * Defines the type reflector that uses reflection to get types
 */
class ReflectionTypeReflector implements ITypeReflector
{
    /** @var array The type reflection cache */
    private array $cache = [
        'parameters' => [],
        'properties' => [],
        'returnTypes' => []
    ];

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method or parameter did not exist
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array
    {
        if (isset($this->cache['parameters'][$class][$method][$parameter])) {
            return $this->cache['parameters'][$class][$method][$parameter];
        }

        $reflectedMethod = new ReflectionMethod($class, $method);

        foreach ($reflectedMethod->getParameters() as $reflectedParameter) {
            if ($reflectedParameter->getName() !== $parameter) {
                continue;
            }

            if (($type = $reflectedParameter->getType()) === null) {
                $types = null;
            } else {
                $types = $this->createTypesFromReflectionType($type);
            }

            if (!isset($this->cache['parameters'][$class])) {
                $this->cache['parameters'][$class] = [];
            }

            if (!isset($this->cache['parameters'][$class][$method])) {
                $this->cache['parameters'][$class][$method] = [];
            }

            $this->cache['parameters'][$class][$method][$parameter] = $types;

            return $types;
        }

        throw new ReflectionException("Parameter $parameter does not exist in $class::$method()");
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the property does not exist
     */
    public function getPropertyTypes(string $class, string $property): ?array
    {
        if (isset($this->cache['properties'][$class][$property])) {
            return $this->cache['properties'][$class][$property];
        }

        $reflectedProperty = new ReflectionProperty($class, $property);

        if (($type = $reflectedProperty->getType()) === null) {
            return null;
        }

        $types = $this->createTypesFromReflectionType($type);

        if (!isset($this->cache['properties'][$class])) {
            $this->cache['properties'][$class] = [];
        }

        $this->cache['properties'][$class][$property] = $types;

        return $types;
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method does not exist
     */
    public function getReturnTypes(string $class, string $method): ?array
    {
        if (isset($this->cache['returnTypes'][$class][$method])) {
            return $this->cache['returnTypes'][$class][$method];
        }

        $reflectedMethod = new ReflectionMethod($class, $method);

        if (($type = $reflectedMethod->getReturnType()) === null) {
            return null;
        }

        $types = $this->createTypesFromReflectionType($type);

        if (!isset($this->cache['returnTypes'][$class])) {
            $this->cache['returnTypes'][$class] = [];
        }

        $this->cache['returnTypes'][$class][$method] = $types;

        return $types;
    }

    /**
     * Creates types from a reflection type
     *
     * @param ReflectionType $reflectedType The reflected type to convert
     * @return Type[] The list of types
     */
    private function createTypesFromReflectionType(ReflectionType $reflectedType): array
    {
        // TODO: When PHP 8 is released, add support for ReflectionUnionType
        $isNullable = $reflectedType->allowsNull();
        $typeName = $reflectedType->getName();

        if ($typeName === 'array') {
            return [new Type('array', null, $isNullable, true)];
        }

        if ($typeName === 'void') {
            return [new Type('null', null, $isNullable)];
        }

        if (!Type::isPhpType($typeName)) {
            return [new Type('object', $typeName, $isNullable)];
        }

        return [new Type($typeName, null, $isNullable)];
    }
}
