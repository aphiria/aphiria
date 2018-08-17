<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use ErrorException;
use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Opulence\Net\Http\Formatting\ResponseWriter;
use Opulence\Net\Http\RequestContext;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Defines the exception handler
 */
class ExceptionHandler implements IExceptionHandler
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var LoggerInterface The logger */
    protected $logger;
    /** @var IExceptionResponseFactory The exception response factory */
    protected $exceptionResponseFactory;
    /** @var ResponseWriter What to use to write a response */
    protected $responseWriter;
    /** @var int $loggedLevels The bitwise value of error levels that are to be logged */
    protected $loggedLevels;
    /** @var int $thrownLevels The bitwise value of error levels that are to be thrown as exceptions */
    protected $thrownLevels;
    /** @var array The list of exception classes to not log */
    protected $exceptionsNotLogged;
    /** @var RequestContext|null The current request context, or null if there is none */
    protected $requestContext;

    /**
     * @param LoggerInterface|null $logger The logger to use, or null if using the default error logger
     * @param IExceptionResponseFactory|null $exceptionResponseFactory The exception response factory
     * @param ResponseWriter $responseWriter What to use to write a response
     * @param int|null $loggedLevels The bitwise value of error levels that are to be logged
     * @param int|null $thrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @param array $exceptionsNotLogged The exception or list of exceptions to not log when thrown
     */
    public function __construct(
        LoggerInterface $logger = null,
        IExceptionResponseFactory $exceptionResponseFactory = null,
        ResponseWriter $responseWriter = null,
        int $loggedLevels = null,
        int $thrownLevels = null,
        array $exceptionsNotLogged = []
    ) {
        if ($logger === null) {
            $logger = new Logger(self::DEFAULT_LOGGER_NAME);
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->logger = $logger;
        $this->exceptionResponseFactory = $exceptionResponseFactory ?? new ExceptionResponseFactory();
        $this->responseWriter = $responseWriter ?? new ResponseWriter();
        $this->loggedLevels = $loggedLevels ?? 0;
        $this->thrownLevels = $thrownLevels ?? (E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED));
        $this->exceptionsNotLogged = $exceptionsNotLogged;
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
        if ($this->shouldLogError($level)) {
            $this->logger->log($level, $message, $context);
        }

        if ($this->shouldThrowError($level)) {
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

        if ($this->shouldLogException($ex)) {
            $this->logger->error($ex);
        }

        $response = $this->exceptionResponseFactory->createResponseFromException($ex, $this->requestContext);
        $this->responseWriter->writeResponse($response);
    }

    /**
     * @inheritdoc
     */
    public function handleShutdown(): void
    {
        $error = \error_get_last();

        if ($error !== null && \in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(
                new FatalErrorException($error['message'], $error['type'], 0, $error['file'], $error['line'])
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        \ini_set('display_errors', 'off');
        \error_reporting(-1);
        \set_error_handler([$this, 'handleError']);
        \set_exception_handler([$this, 'handleException']);
        \register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * @inheritdoc
     */
    public function setRequestContext(RequestContext $requestContext): void
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Determines whether or not the error level is loggable
     *
     * @param int $level The bitwise level
     * @return bool True if the level is loggable, otherwise false
     */
    protected function shouldLogError(int $level): bool
    {
        return ($this->loggedLevels & $level) !== 0;
    }

    /**
     * Determines whether or not an exception should be logged
     *
     * @param Exception $ex The exception to check
     * @return bool True if the exception should be logged, otherwise false
     */
    protected function shouldLogException(Exception $ex): bool
    {
        return !\in_array(\get_class($ex), $this->exceptionsNotLogged);
    }

    /**
     * Gets whether or not the error level is throwable
     *
     * @param int $level The bitwise level
     * @return bool True if the level is throwable, otherwise false
     */
    protected function shouldThrowError(int $level): bool
    {
        return ($this->thrownLevels & $level) !== 0;
    }
}
