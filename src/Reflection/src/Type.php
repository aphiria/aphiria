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

/**
 * Defines a PHP type
 *
 * Note:  This class takes inspiration from Symfony's Type class
 * @link https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/PropertyInfo/Type.php
 */
final class Type
{
    /** @var string The PHP array type */
    public const PHP_ARRAY = 'array';
    /** @var string The PHP boolean type */
    public const PHP_BOOL = 'bool';
    /** @var string The PHP callable type */
    public const PHP_CALLABLE = 'callable';
    /** @var string The PHP float type */
    public const PHP_FLOAT = 'float';
    /** @var string The PHP integer type */
    public const PHP_INT = 'int';
    /** @var string The PHP iterable type */
    public const PHP_ITERABLE = 'iterable';
    /** @var string The PHP mixed type */
    public const PHP_MIXED = 'mixed';
    /** @var string The PHP null type */
    public const PHP_NULL = 'null';
    /** @var string The PHP object type */
    public const PHP_OBJECT = 'object';
    /** @var string The PHP resource type */
    public const PHP_RESOURCE = 'resource';
    /** @var string The PHP string type */
    public const PHP_STRING = 'string';
    /** @var string The name of the PHP type, eg int, string, object */
    private string $phpType;
    /** @var string|null The name of the class */
    private ?string $class;
    /** @var bool Whether or not this is nullable */
    private bool $isNullable;
    /** @var bool Whether or not this type is iterable */
    private bool $isIterable;
    /** @var Type|null The type of key, if this is an iterable type */
    private ?Type $iterableKeyType;
    /** @var Type|null The type of value, if this is an iterable type */
    private ?Type $iterableValueType;

    /**
     * @param string $phpType The PHP type, eg int, string, object
     * @param string|null $class The name of the class, if this was a class
     * @param bool $isNullable Whether or not this is nullable
     * @param bool $isIterable Whether or not the type is iterable
     * @param Type|null $iterableKeyType The type of key, if this is an iterable type
     * @param Type|null $iterableValueType The type of value, if this is an iterable type
     */
    public function __construct(
        string $phpType,
        string $class = null,
        bool $isNullable = false,
        bool $isIterable = false,
        Type $iterableKeyType = null,
        Type $iterableValueType = null
    ) {
        $this->phpType = $phpType;
        $this->class = $class;
        $this->isNullable = $isNullable;
        $this->isIterable = $isIterable;
        $this->iterableKeyType = $iterableKeyType;
        $this->iterableValueType = $iterableValueType;
    }

    /**
     * Checks whether or not a type is a PHP type
     *
     * @param string $type The type to check
     * @return bool True if the type is a PHP type, otherwise false
     */
    public static function isPhpType(string $type): bool
    {
        switch ($type) {
            case self::PHP_ARRAY:
            case self::PHP_BOOL:
            case self::PHP_CALLABLE:
            case self::PHP_FLOAT:
            case self::PHP_INT:
            case self::PHP_ITERABLE:
            case self::PHP_MIXED:
            case self::PHP_NULL:
            case self::PHP_OBJECT:
            case self::PHP_RESOURCE:
            case self::PHP_STRING:
                return true;
            default:
                return false;
        }
    }

    /**
     * Gets the name of the class
     *
     * @return string|null The name of the class, or null if it's not a class
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Gets the type of the key, if this is an iterable type
     *
     * @return Type|null The type, or null if it's not iterable
     */
    public function getIterableKeyType(): ?Type
    {
        return $this->iterableKeyType;
    }

    /**
     * Gets the type of the value, if this is an iterable type
     *
     * @return Type|null The type, or null if it's not iterable
     */
    public function getIterableValueType(): ?Type
    {
        return $this->iterableValueType;
    }

    /**
     * Gets the PHP type, eg string, int, object
     *
     * @return string The PHP type
     */
    public function getPhpType(): string
    {
        return $this->phpType;
    }

    /**
     * Gets whether or not this type is a class
     *
     * @return bool True if this type represents a class, otherwise false
     */
    public function isClass(): bool
    {
        return $this->class !== null;
    }

    /**
     * Gets whether or not this type is iterable
     *
     * @return bool True if this is an iterable type, otherwise false
     */
    public function isIterable(): bool
    {
        return $this->isIterable;
    }

    /**
     * Gets whether or not this type is nullable
     *
     * @return bool True if this type is nullable, otherwise false
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
