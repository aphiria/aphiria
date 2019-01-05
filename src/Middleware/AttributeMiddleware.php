<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Middleware;

/**
 * Defines an attribute middleware
 */
abstract class AttributeMiddleware implements IMiddleware
{
    /** @var array The middleware attributes */
    private $attributes = [];

    /**
     * Sets the attributes
     *
     * @param array $attributes The attributes to set
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Gets the value of a attribute
     *
     * @param string $name The name of the attribute to get
     * @param mixed $default The default value
     * @return mixed|null The attribute's value if it is set, otherwise null
     */
    protected function getAttribute(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }
}
