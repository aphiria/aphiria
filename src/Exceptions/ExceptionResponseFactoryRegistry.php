<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Closure;

/**
 * Defines the exception response factory registry
 */
class ExceptionResponseFactoryRegistry
{
    /** @var Closure[] The mapping of exception types to response factories */
    private $factories = [];

    /**
     * Gets the factory for a particular exception
     *
     * @param string $exceptionType The type of exception whose factory we want
     * @return Closure|null The response factory if one was found, otherwise null
     */
    public function getFactory(string $exceptionType): ?Closure
    {
        if (!isset($this->factories[$exceptionType])) {
            return null;
        }

        return $this->factories[$exceptionType];
    }

    /**
     * Registers a response factory for an exception type
     *
     * @param string $exceptionType The type the response factory applies to
     * @param Closure $responseFactory The response factory that takes in an exception instance and request context
     */
    public function registerFactory(string $exceptionType, Closure $responseFactory): void
    {
        $this->factories[$exceptionType] = $responseFactory;
    }
}
