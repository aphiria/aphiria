<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Middleware;

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
