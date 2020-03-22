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
use ErrorException;
use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Defines the global exception handler
 */
class GlobalExceptionHandler implements IGlobalExceptionHandler
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var IExceptionRenderer The underlying exception renderer */
    protected IExceptionRenderer $exceptionRenderer;
    /** @var LoggerInterface The PSR-3 logger */
    protected LoggerInterface $logger;
    /** @var Closure[] The mapping of exception types to log level factories */
    private array $logLevelFactories = [];

    /**
     * @param IExceptionRenderer $exceptionRenderer The underlying exception renderer
     * @param LoggerInterface|null $logger The PSR-3 logger
     */
    public function __construct(
        IExceptionRenderer $exceptionRenderer,
        LoggerInterface $logger = null
    ) {
        $this->exceptionRenderer = $exceptionRenderer;
        $this->logger = $logger  ?? new Logger(self::DEFAULT_LOGGER_NAME, [new ErrorLogHandler()]);
    }

    /**
     * @inheritdoc
     */
    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        array $context = []
    ): void {
        if ((\error_reporting() & $level) !== 0) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @inheritdoc
     */
    public function handleException(Throwable $ex): void
    {
        // It's Throwable, but not an Exception
        if (!$ex instanceof Exception) {
            $ex = new FatalThrowableError($ex);
        }

        if (isset($this->logLevelFactories[\get_class($ex)])) {
            $logLevel = $this->logLevelFactories[\get_class($ex)]($ex);
        } else {
            $logLevel = LogLevel::ERROR;
        }

        $this->logger->{$logLevel}($ex);
        $this->exceptionRenderer->render($ex);
    }

    /**
     * @inheritdoc
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $this->handleException(
                new FatalErrorException($error['message'], $error['type'], 0, $error['file'], $error['line'])
            );
        }
    }

    /**
     * Registers an exception log level factory
     *
     * @param string $exceptionType The exception whose factory we're registering
     * @param Closure $factory The factory that takes in an exception of the input type and returns a PSR-3 log level
     */
    public function registerLogLevelFactory(string $exceptionType, Closure $factory): void
    {
        $this->logLevelFactories[$exceptionType] = $factory;
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

    /**
     * @inheritdoc
     */
    public function registerWithPhp(): void
    {
        ini_set('display_errors', 'off');
        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
}
