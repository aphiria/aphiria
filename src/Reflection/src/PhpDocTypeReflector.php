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
use ReflectionProperty;

/**
 * Defines the parameter type that uses PHPDoc to infer the type
 */
class PhpDocTypeReflector implements ITypeReflector
{
    /** @var DocBlockFactoryInterface The doc block factory */
    private DocBlockFactoryInterface $docBlockFactory;

    public function __construct(DocBlockFactoryInterface $docBlockFactory = null)
    {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance();
    }

    /**
     * @inheritdoc
     */
    public function getParameterTypes(string $class, string $method, string $parameter): ?array
    {
        // TODO: Implement getType() method.
    }

    /**
     * @inheritdoc
     */
    public function getPropertyTypes(string $class, string $property): ?array
    {
        try {
            $reflectedProperty = new ReflectionProperty($class, $property);
            $docBlock = $this->docBlockFactory->create($reflectedProperty);
        } catch (ReflectionException | InvalidArgumentException $ex) {
            // Exceptions here can be caused by a property not having any PHPDoc.  So, just swallow them.
            return null;
        }

        $types = null;

        foreach ($docBlock->getTagsByName('var') as $tag) {
            if (!$tag instanceof TagWithType) {
                continue;
            }

            if (($typesForThisTag = $this->createTypesFromPhpDocType($tag->getType())) !== null) {
                if ($types === null) {
                    $types = [];
                }

                $types = [...$types, ...$typesForThisTag];
            }
        }

        return $types;
    }

    /**
     * @inheritdoc
     */
    public function getReturnTypes(string $class, string $method): ?array
    {
        // TODO: Implement getReturnTypes() method.
    }

    /**
     * Creates a list of types from a PHPDoc
     *
     * @param PhpDocType $docType The PHPDoc type
     * @param bool $isNullable Whether or not the type is nullable
     * @return Type[]|null The list of types if they could be created, otherwise null
     */
    private function createTypesFromPhpDocType(PhpDocType $docType, bool $isNullable = false): ?array
    {
        $isNullable = $isNullable || $docType instanceof Null_ || $docType instanceof Nullable;

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

                if (($singleTypes = $this->createTypesFromPhpDocType($singleDocType, $isNullable)) !== null) {
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
                $class = \substr($phpType, 1);
            }

            $keyTypes = $this->createTypesFromPhpDocType($docType->getKeyType());
            $valueTypes = $this->createTypesFromPhpDocType($docType->getValueType());

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
            if ($serializedDocType === 'mixed[]') {
                $keyType = $valueType = null;
            } else {
                $keyType = new Type('int');
                $valueTypes = $this->createTypesFromPhpDocType(
                    (new TypeResolver())->resolve(substr($serializedDocType, 0, -2))
                );
                $valueType = $valueTypes === null || \count($valueTypes) === 0 ? null : $valueTypes[0];
            }

            return [new Type('array', null, $isNullable, true, $keyType, $valueType)];
        }

        $normalizedDocType = self::normalizeType($serializedDocType);
        $class = null;

        if (!Type::isPhpType($normalizedDocType)) {
            $class = \substr($normalizedDocType, 1);
            $normalizedDocType = 'object';
        }

        if ($normalizedDocType === 'array') {
            return [new Type('array', null, $isNullable, true, null, null)];
        }

        return [new Type($normalizedDocType, $class, $isNullable)];
    }

    /**
     * Normalizes a PHPDoc type to a PHP type
     *
     * @param string $phpDocType The PHPDoc type to normalize
     * @return string The normalized type
     */
    private static function normalizeType(string $phpDocType): string
    {
        switch ($phpDocType) {
            case 'boolean':
                return 'bool';
            case 'double':
                return 'float';
            case 'integer':
                return 'int';
            case 'void':
                return 'null';
            default:
                return $phpDocType;
        }
    }
}
