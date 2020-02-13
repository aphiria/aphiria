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

use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\StreamResponseWriter;
use ErrorException;
use Exception;
use Throwable;

/**
 * The exception handler that handles exceptions that were unhandled in the application
 */
class GlobalExceptionHandler
{
    /** @var IExceptionResponseFactory The factory that create exception responses */
    private IExceptionResponseFactory $exceptionResponseFactory;
    /** @var IExceptionLogger The exception logger */
    private IExceptionLogger $logger;
    /** @var int The bitwise value of error levels that are to be thrown as exceptions */
    protected int $errorThrownLevels;
    /** @var IResponseWriter What to use to write a response */
    protected IResponseWriter $responseWriter;

    /**
     * @param IExceptionResponseFactory|null $exceptionResponseFactory The factory that create exception responses, or null if using the default factory
     * @param IExceptionLogger|null $logger The exception logger
     * @param int $errorThrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @param IResponseWriter $responseWriter What to use to write a response
     */
    public function __construct(
        IExceptionResponseFactory $exceptionResponseFactory = null,
        IExceptionLogger $logger = null,
        int $errorThrownLevels = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED),
        IResponseWriter $responseWriter = null
    ) {
        $this->exceptionResponseFactory = $exceptionResponseFactory ?? new ExceptionResponseFactory();
        $this->logger = $logger ?? new ExceptionLogger();
        $this->errorThrownLevels = $errorThrownLevels;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
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
        $this->logger->logError($level, $message, $file, $line, $context);

        if ($this->shouldThrowError($level)) {
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

        $this->logger->logException($ex);
        $response = $this->exceptionResponseFactory->createResponseFromException($ex, null);
        $this->responseWriter->writeResponse($response);
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

    /**
     * Gets whether or not the error level is throwable
     *
     * @param int $level The bitwise level
     * @return bool True if the level is throwable, otherwise false
     */
    protected function shouldThrowError(int $level): bool
    {
        return ($this->errorThrownLevels & $level) !== 0;
    }
}
