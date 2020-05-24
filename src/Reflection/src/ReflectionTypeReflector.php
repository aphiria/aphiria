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
    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method or parameter did not exist
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array
    {
        $reflectedMethod = new ReflectionMethod($class, $method);

        foreach ($reflectedMethod->getParameters() as $reflectedParameter) {
            if ($reflectedParameter->getName() !== $parameter) {
                continue;
            }

            if (($type = $reflectedParameter->getType()) === null) {
                return null;
            }

            return $this->createTypesFromReflectionType($type);
        }

        throw new ReflectionException("Parameter $parameter does not exist in $class::$method()");
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the property does not exist
     */
    public function getPropertyTypes(string $class, string $property): ?array
    {
        $reflectedProperty = new ReflectionProperty($class, $property);

        if (($type = $reflectedProperty->getType()) === null) {
            return null;
        }

        return $this->createTypesFromReflectionType($type);
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method does not exist
     */
    public function getReturnTypes(string $class, string $method): ?array
    {
        $reflectedMethod = new ReflectionMethod($class, $method);

        if (($type = $reflectedMethod->getReturnType()) === null) {
            return null;
        }

        return $this->createTypesFromReflectionType($type);
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
