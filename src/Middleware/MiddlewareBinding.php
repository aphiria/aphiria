<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Middleware;

/**
 * Defines a middleware binding
 */
class MiddlewareBinding
{
    /** @var string The name of the middleware class */
    public $className;
    /** @var array The name => value mapping of attributes bound to the middleware */
    public $attributes;

    /**
     * @param string $className The name of the middleware class
     * @param array $attributes The name => value mapping of attributes bound to the middleware
     */
    public function __construct(string $className, array $attributes = [])
    {
        $this->className = $className;
        $this->attributes = $attributes;
    }
}
