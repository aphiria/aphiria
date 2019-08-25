<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines an attribute middleware
 */
abstract class AttributeMiddleware implements IMiddleware
{
    /** @var array The middleware attributes */
    private array $attributes = [];

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
