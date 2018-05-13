<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization\Encoding;

use Closure;

/**
 * Defines an object property
 */
class Property
{
    /** @var string The name of the property */
    private $name;
    /** @var string The type of the property */
    private $type;
    /** @var Closure The closure that takes in an instance of the object and returns the value of the property */
    private $getter;
    /** @var bool Whether or not this property is an array of values */
    private $isArrayOfType;
    /** @var bool Whether or not this property is nullable */
    private $isNullable;

    /**
     *
     * @param string $name The name of the property
     * @param string $type The type of the property
     * @param Closure $getter The closure that takes in an instance of the object and returns the value of the property
     * @param bool $isArrayOfType Whether or not this property is an array of values
     * @param bool $isNullable Whether or not this property is nullable
     */
    public function __construct(
        string $name,
        string $type,
        Closure $getter,
        bool $isArrayOfType = false,
        bool $isNullable = false
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->getter = $getter;
        $this->isArrayOfType = $isArrayOfType;
        $this->isNullable = $isNullable;
    }

    /**
     * Gets the name of the property
     *
     * @return string The name of the property
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the type of the property
     *
     * @return string The type of the property
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the value of the property given an input object
     *
     * @param \object $object The object to get the property value from
     * @return mixed The value of the property
     */
    public function getValue(object $object)
    {
        return ($this->getter)($object);
    }

    /**
     * Gets whether or not the property value is an array of values
     *
     * @return bool True if the property is an array of values, otherwise false
     */
    public function isArrayOfType(): bool
    {
        return $this->isArrayOfType;
    }

    /**
     * Gets whether or not the property value is nullable
     *
     * @return bool True if the property is nullable, otherwise false
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
