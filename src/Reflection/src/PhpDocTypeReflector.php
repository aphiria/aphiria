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

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Type as PhpDocType;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Defines the parameter type that uses PHPDoc to infer the type
 *
 * Note:  This reflector takes inspiration from Symfony's PhpDocTypeHelper
 * @link https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/PropertyInfo/Util/PhpDocTypeHelper.php
 */
class PhpDocTypeReflector implements ITypeReflector
{
    /** @var DocBlockFactoryInterface The doc block factory */
    private DocBlockFactoryInterface $docBlockFactory;
    /** @var array The type reflection cache */
    private array $cache = [
        'parameters' => [],
        'properties' => [],
        'returnTypes' => []
    ];

    /**
     * @param DocBlockFactoryInterface|null $docBlockFactory The doc block factory to use
     */
    public function __construct(DocBlockFactoryInterface $docBlockFactory = null)
    {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method or parameter does not exist
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array
    {
        if (isset($this->cache['parameters'][$class][$method][$parameter])) {
            return $this->cache['parameters'][$class][$method][$parameter];
        }

        try {
            $reflectedMethod = new ReflectionMethod($class, $method);
            $methodHasParameter = false;

            foreach ($reflectedMethod->getParameters() as $reflectionParameter) {
                if ($reflectionParameter->getName() === $parameter) {
                    $methodHasParameter = true;
                    break;
                }
            }

            if (!$methodHasParameter) {
                throw new ReflectionException("Parameter $parameter does not exist in $class::$method()");
            }

            $docBlock = $this->docBlockFactory->create($reflectedMethod);
        } catch (InvalidArgumentException $ex) {
            // Exceptions here can be caused by a method not having any PHPDoc.  So, just swallow them.
            return null;
        }

        $types = $this->getTypesFromDocBlock($docBlock, 'param', $reflectionParameter, $parameter);
        $this->cacheTypes('parameters', $types, $class, $method, $parameter);

        return $types;
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the property could not be reflected
     */
    public function getPropertyTypes(string $class, string $property): ?array
    {
        if (isset($this->cache['properties'][$class][$property])) {
            return $this->cache['properties'][$class][$property];
        }

        try {
            $reflectedProperty = new ReflectionProperty($class, $property);
            $docBlock = $this->docBlockFactory->create($reflectedProperty);
        } catch (InvalidArgumentException $ex) {
            // Exceptions here can be caused by a property not having any PHPDoc.  So, just swallow them.
            return null;
        }

        $types = $this->getTypesFromDocBlock($docBlock, 'var', $reflectedProperty);
        $this->cacheTypes('properties', $types, $class, $property);

        return $types;
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if the method did not exist
     */
    public function getReturnTypes(string $class, string $method): ?array
    {
        if (isset($this->cache['returnTypes'][$class][$method])) {
            return $this->cache['returnTypes'][$class][$method];
        }

        try {
            $reflectedMethod = new ReflectionMethod($class, $method);
            $docBlock = $this->docBlockFactory->create($reflectedMethod);
        } catch (InvalidArgumentException $ex) {
            // Exceptions here can be caused by a method not having any PHPDoc.  So, just swallow them.
            return null;
        }

        $types = $this->getTypesFromDocBlock($docBlock, 'return', $reflectedMethod);
        $this->cacheTypes('returnTypes', $types, $class, $method);

        return $types;
    }

    /**
     * Caches types for a specific path
     *
     * @param string $key The initial cache key
     * @param array|null $types The types to cache
     * @param string ...$args The cache keys
     */
    private function cacheTypes(string $key, ?array $types, string ...$args): void
    {
        $currValue = &$this->cache[$key];

        // Populate each level of the cache map
        foreach ($args as $arg) {
            if (!isset($currValue[$arg])) {
                $currValue[$arg] = [];
            }

            $currValue = &$currValue[$arg];
        }

        $currValue = $types;
    }

    /**
     * Creates a list of types from a PHPDoc
     *
     * @param PhpDocType $docType The PHPDoc type
     * @param ReflectionMethod|ReflectionProperty|ReflectionParameter $reflectedData The reflected data
     * @param bool $isNullable Whether or not the type is nullable
     * @return Type[]|null The list of types if they could be created, otherwise null
     */
    private function createTypesFromPhpDocType(PhpDocType $docType, $reflectedData, bool $isNullable = false): ?array
    {
        $isNullable = $isNullable || $docType instanceof Null_ || $docType instanceof Nullable;

        // Eg ?string, get string
        if ($docType instanceof Nullable) {
            $docType = $docType->getActualType();
        }

        if ($docType instanceof Compound) {
            $types = null;

            // First check if any of the PHPDoc types were nullable
            /** @var PhpDocType[] $docType */
            foreach ($docType as $singleDocType) {
                $isNullable = $isNullable || $singleDocType instanceof Null_ || $singleDocType instanceof Nullable;
            }

            // Then, convert them
            foreach ($docType as $singleDocType) {
                // We don't want to add "null" as a type
                if ($singleDocType instanceof Null_) {
                    continue;
                }

                if (($singleTypes = $this->createTypesFromPhpDocType($singleDocType, $reflectedData, $isNullable)) !== null) {
                    if ($types === null) {
                        $types = [];
                    }

                    $types = [...$types, ...$singleTypes];
                }
            }

            return $types;
        }

        if ($docType instanceof Collection) {
            $phpType = (string)$docType->getFqsen();
            $class = null;

            if (!Type::isPhpType($phpType)) {
                // Types should not have preceding slashes
                $class = ltrim($phpType, '\\');
                $phpType = 'object';
            }

            $keyTypes = $this->createTypesFromPhpDocType($docType->getKeyType(), $reflectedData);
            $valueTypes = $this->createTypesFromPhpDocType($docType->getValueType(), $reflectedData);

            return [
                new Type(
                    $phpType,
                    $class,
                    $isNullable,
                    true,
                    $keyTypes !== null && \count($keyTypes) > 0 ? $keyTypes[0] : null,
                    $valueTypes !== null && \count($valueTypes) > 0 ? $valueTypes[0] : null
                )
            ];
        }

        $serializedDocType = (string)$docType;

        // Handle something like string[]
        if (substr($serializedDocType, -2) === '[]') {
            $keyType = new Type('int');
            $valueTypes = $this->createTypesFromPhpDocType(
                (new TypeResolver())->resolve(substr($serializedDocType, 0, -2)),
                $reflectedData
            );
            $valueType = $valueTypes === null || \count($valueTypes) === 0 ? null : $valueTypes[0];

            return [new Type('array', null, $isNullable, true, $keyType, $valueType)];
        }

        $class = null;

        if (!Type::isPhpType($serializedDocType)) {
            // Types should not have preceding slashes
            $class = \ltrim($serializedDocType, '\\');
            $serializedDocType = 'object';

            if ($class === 'self' || $class === '$this') {
                $class = $reflectedData->getDeclaringClass()->getName();
            }
        }

        if ($serializedDocType === 'array') {
            return [new Type('array', null, $isNullable, true, null, null)];
        }

        return [new Type($serializedDocType, $class, $isNullable)];
    }

    /**
     * Gets all the types from a doc block for a particular tag
     *
     * @param DocBlock $docBlock The PHPDoc block to search through
     * @param string $phpDocTagName The name of the PHPDoc tag name to search for
     * @param ReflectionMethod|ReflectionProperty|ReflectionParameter $reflectedData The reflected data
     * @param string|null $variableName The name of the variable whose doc block we want, or null if not filtering by variable
     * @return array|null The list of return types if there any, otherwise null
     */
    private function getTypesFromDocBlock(
        DocBlock $docBlock,
        string $phpDocTagName,
        $reflectedData,
        string $variableName = null
    ): ?array {
        $types = null;

        /** @var TagWithType $tag */
        foreach ($docBlock->getTagsByName($phpDocTagName) as $tag) {
            // Skip if this is a @param doc block for a different variable than desired
            if ($variableName !== null && $tag instanceof Param && $tag->getVariableName() !== $variableName) {
                continue;
            }

            if (($typesForThisTag = $this->createTypesFromPhpDocType($tag->getType(), $reflectedData)) !== null) {
                if ($types === null) {
                    $types = [];
                }

                $types = [...$types, ...$typesForThisTag];
            }
        }

        return $types;
    }
}
