<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Closure;

/**
 * Defines the exception response factory registry
 */
final class ExceptionResponseFactoryRegistry
{
    /** @var Closure[] The mapping of exception types to response factories */
    private array $factories = [];

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
     * @param Closure $responseFactory The response factory that takes in an exception instance, nullable request, and a negotiated response factory
     */
    public function registerFactory(string $exceptionType, Closure $responseFactory): void
    {
        $this->factories[$exceptionType] = $responseFactory;
    }

    /**
     * Registers a response factory for an exception type
     *
     * @param Closure[] $exceptionTypesToFactories The exception types to factories
     */
    public function registerManyFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $responseFactory) {
            $this->registerFactory($exceptionType, $responseFactory);
        }
    }
}
