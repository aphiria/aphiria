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
class GlobalExceptionHandler
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var IExceptionHandler The underlying exception handler */
    protected IExceptionHandler $exceptionHandler;
    /** @var LoggerInterface The PSR-3 logger */
    protected LoggerInterface $logger;
    /** @var LogLevelFactoryRegistry The registry of exception log level factories */
    protected LogLevelFactoryRegistry $logLevelFactories;
    /** @var int The bitwise value of error levels that are to be thrown as exceptions */
    protected int $errorThrownLevels;

    /**
     * @param IExceptionHandler $exceptionHandler The underlying exception handler
     * @param LoggerInterface|null $logger The PSR-3 logger
     * @param LogLevelFactoryRegistry|null $logLevelFactories The registry of exception log level factories
     * @param int $errorThrownLevels The bitwise value of error levels that are to be thrown as exceptions
     */
    public function __construct(
        IExceptionHandler $exceptionHandler,
        LoggerInterface $logger = null,
        LogLevelFactoryRegistry $logLevelFactories = null,
        int $errorThrownLevels = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED)
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger  ?? new Logger(self::DEFAULT_LOGGER_NAME, [new ErrorLogHandler()]);
        $this->logLevelFactories = $logLevelFactories ?? new LogLevelFactoryRegistry();
        $this->errorThrownLevels = $errorThrownLevels;
    }

    /**
     * Handles an error
     *
     * @param int $level The level of the error
     * @param string $message The message
     * @param string $file The file the error occurred in
     * @param int $line The line number the error occurred at
     * @param array $context The symbol table
     * @throws ErrorException Thrown because the error is converted to an exception
     */
    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        array $context = []
    ): void {
        $this->logger->log($level, $message, $context);

        if (($this->errorThrownLevels & $level) !== 0) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handles an exception
     *
     * @param Throwable $ex The exception to handle
     */
    public function handleException(Throwable $ex): void
    {
        // It's Throwable, but not an Exception
        if (!$ex instanceof Exception) {
            $ex = new FatalThrowableError($ex);
        }

        $logLevelFactory = $this->logLevelFactories->getFactory(get_class($ex));
        $logLevel = $logLevelFactory === null ? LogLevel::ERROR : $logLevelFactory($ex);
        $this->logger->{$logLevel}($ex);

        $this->exceptionHandler->handle($ex);
    }

    /**
     * Handles a PHP shutdown
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
     * Registers the exception and error handlers with PHP
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
