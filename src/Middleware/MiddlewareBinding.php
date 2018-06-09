<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Middleware;

/**
 * Defines a middleware binding
 */
class MiddlewareBinding
{
    /** @var string The name of the middleware class */
    private $className;
    /** @var array The name => value mapping of attributes bound to the middleware */
    private $attributes;

    /**
     * @param string $className The name of the middleware class
     * @param array $attributes The name => value mapping of attributes bound to the middleware
     */
    public function __construct(string $className, array $attributes = [])
    {
        $this->className = $className;
        $this->attributes = $attributes;
    }

    /**
     * Gets the mapping of attribute names => values for the middleware
     *
     * @return array The mapping of attribute names => values
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Gets the name of the middleware class
     *
     * @return string The middleware class name
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
