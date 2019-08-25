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
 * Defines the registry of exception log level factories
 */
final class ExceptionLogLevelFactoryRegistry
{
    /** @var Closure[] The mapping of exception types to log level factories */
    private array $factories;

    /**
     * Gets the factory for a particular exception
     *
     * @param string $exceptionType The type of exception whose factory we want
     * @return Closure|null The exception log level factory if one was found, otherwise null
     */
    public function getFactory(string $exceptionType): ?Closure
    {
        if (!isset($this->factories[$exceptionType])) {
            return null;
        }

        return $this->factories[$exceptionType];
    }

    /**
     * Registers an exception log level factory
     *
     * @param string $exceptionType The exception whose factory we're registering
     * @param Closure $factory The factory that takes in an exception of the input type and returns a PSR-3 log level
     */
    public function registerFactory(string $exceptionType, Closure $factory): void
    {
        $this->factories[$exceptionType] = $factory;
    }

    /**
     * Registers an exception log level factory for an exception type
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
