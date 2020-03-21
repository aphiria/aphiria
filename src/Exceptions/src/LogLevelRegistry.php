<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Closure;
use Exception;

/**
 * Defines the registry of exception log level factories
 */
final class LogLevelRegistry
{
    /** @var Closure[] The mapping of exception types to log level factories */
    private array $factories = [];

    /**
     * Gets the factory for a particular exception
     *
     * @param Exception $ex The exception whose log level we want
     * @return string|null The exception log level if one was found, otherwise null
     */
    public function getLogLevel(Exception $ex): ?string
    {
        if (isset($this->factories[\get_class($ex)])) {
            return $this->factories[\get_class($ex)]($ex);
        }

        return null;
    }

    /**
     * Registers an exception log level factory
     *
     * @param string $exceptionType The exception whose factory we're registering
     * @param Closure $factory The factory that takes in an exception of the input type and returns a PSR-3 log level
     */
    public function registerLogLevelFactory(string $exceptionType, Closure $factory): void
    {
        $this->factories[$exceptionType] = $factory;
    }

    /**
     * Registers an exception log level factory for an exception type
     *
     * @param Closure[] $exceptionTypesToFactories The exception types to factories
     */
    public function registerManyLogLevelFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $responseFactory) {
            $this->registerLogLevelFactory($exceptionType, $responseFactory);
        }
    }
}
