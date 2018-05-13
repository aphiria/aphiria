<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

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

    /**
     *
     * @param string $name The name of the property
     * @param string $type The type of the property
     * @param Closure $getter The closure that takes in an instance of the object and returns the value of the property
     */
    public function __construct(string $name, string $type, Closure $getter)
    {
        $this->name = $name;
        $this->type = $type;
        $this->getter = $getter;
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
}
