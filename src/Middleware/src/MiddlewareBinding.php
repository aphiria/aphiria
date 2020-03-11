<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

/**
 * Defines a middleware binding, which is a wrapper around the name of the middleware + any attributes
 */
final class MiddlewareBinding
{
    /** @var string The name of the middleware class */
    public string $className;
    /** @var array The name => value mapping of attributes bound to the middleware */
    public array $attributes;

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
