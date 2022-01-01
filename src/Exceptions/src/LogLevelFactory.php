<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Closure;
use Exception;
use Psr\Log\LogLevel;

/**
 * Defines a factory for PSR-3 log levels
 */
class LogLevelFactory
{
    /** @var array<class-string<Exception>, Closure(Exception): string> The mapping of exception types to log level factories */
    private array $logLevelFactories = [];

    /**
     * Creates a PSR-3 log level from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return string The PSR-3 log level
     */
    public function createLogLevel(Exception $ex): string
    {
        if (isset($this->logLevelFactories[$ex::class])) {
            return $this->logLevelFactories[$ex::class]($ex);
        }

        return LogLevel::ERROR;
    }

    /**
     * Registers an exception log level factory
     *
     * @template T of Exception
     * @param class-string<T> $exceptionType The exception whose factory we're registering
     * @param Closure(T): string $factory The factory that takes in an exception of the input type and returns a PSR-3 log level
     */
    public function registerLogLevelFactory(string $exceptionType, Closure $factory): void
    {
        /** @psalm-suppress InvalidPropertyAssignmentValue This is valid - bug */
        $this->logLevelFactories[$exceptionType] = $factory;
    }

    /**
     * Registers an exception log level factory for an exception type
     *
     * @template T of Exception
     * @param array<class-string<T>, Closure(T): string> $exceptionTypesToFactories The exception types to factories
     */
    public function registerManyLogLevelFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $responseFactory) {
            $this->registerLogLevelFactory($exceptionType, $responseFactory);
        }
    }
}
