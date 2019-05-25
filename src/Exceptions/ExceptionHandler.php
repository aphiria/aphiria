<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Exceptions;

use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\StreamResponseWriter;
use ErrorException;
use Exception;
use function get_class;
use function in_array;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Defines the exception handler
 */
class ExceptionHandler implements IExceptionHandler
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var IExceptionResponseFactory The exception response factory */
    protected $exceptionResponseFactory;
    /** @var LoggerInterface The logger */
    protected $logger;
    /** @var ExceptionLogLevelFactoryRegistry The registry of exception log level factories */
    protected $exceptionLogLevelFactories;
    /** @var array The PSR-3 exception log level that will be logged */
    protected $exceptionLogLevels;
    /** @var int The bitwise value of error levels that are to be logged */
    protected $errorLogLevels;
    /** @var int The bitwise value of error levels that are to be thrown as exceptions */
    protected $errorThrownLevels;
    /** @var IResponseWriter What to use to write a response */
    protected $responseWriter;
    /** @var IHttpRequestMessage|null The current request, or null if there is none */
    protected $request;

    /**
     * @param IExceptionResponseFactory $exceptionResponseFactory The exception response factory
     * @param LoggerInterface|null $logger The logger to use, or null if using the default error logger
     * @param ExceptionLogLevelFactoryRegistry|null $exceptionLogLevelFactories The registry of exception log level factories
     * @param array|null $exceptionLogLevels The PSR-3 exception log levels that will be logged, or null if
     *      using the default levels
     * @param int $errorLogLevels The bitwise value of error levels that are to be logged
     * @param int $errorThrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @param IResponseWriter $responseWriter What to use to write a response
     */
    public function __construct(
        IExceptionResponseFactory $exceptionResponseFactory,
        LoggerInterface $logger = null,
        ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories = null,
        array $exceptionLogLevels = null,
        int $errorLogLevels = 0,
        int $errorThrownLevels = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED),
        IResponseWriter $responseWriter = null
    ) {
        $this->exceptionResponseFactory = $exceptionResponseFactory;
        $this->logger = $logger ?? new Logger(self::DEFAULT_LOGGER_NAME, [new ErrorLogHandler()]);
        $this->exceptionLogLevelFactories = $exceptionLogLevelFactories ?? new ExceptionLogLevelFactoryRegistry();
        $this->exceptionLogLevels = $exceptionLogLevels ?? [
            LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY
            ];
        $this->errorLogLevels = $errorLogLevels;
        $this->errorThrownLevels = $errorThrownLevels;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
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

        $logLevelFactory = $this->exceptionLogLevelFactories->getFactory(get_class($ex));
        $logLevel = $logLevelFactory === null ? LogLevel::ERROR : $logLevelFactory($ex);

        if ($this->shouldLogException($logLevel)) {
            switch ($logLevel) {
                case LogLevel::EMERGENCY:
                    $this->logger->emergency($ex);
                    break;
                case LogLevel::ALERT:
                    $this->logger->alert($ex);
                    break;
                case LogLevel::CRITICAL:
                    $this->logger->critical($ex);
                    break;
                case LogLevel::ERROR:
                    $this->logger->error($ex);
                    break;
                case LogLevel::WARNING:
                    $this->logger->warning($ex);
                    break;
                case LogLevel::NOTICE:
                    $this->logger->notice($ex);
                    break;
                case LogLevel::INFO:
                    $this->logger->info($ex);
                    break;
                case LogLevel::DEBUG:
                    $this->logger->debug($ex);
                    break;
            }
        }

        $response = $this->exceptionResponseFactory->createResponseFromException($ex, $this->request);
        $this->responseWriter->writeResponse($response);
    }

    /**
     * @inheritdoc
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(
                new FatalErrorException($error['message'], $error['type'], 0, $error['file'], $error['line'])
            );
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

    /**
     * @inheritdoc
     */
    public function setRequest(IHttpRequestMessage $request): void
    {
        $this->request = $request;
    }

    /**
     * Determines whether or not the error level is loggable
     *
     * @param int $level The bitwise level
     * @return bool True if the level is loggable, otherwise false
     */
    protected function shouldLogError(int $level): bool
    {
        return ($this->errorLogLevels & $level) !== 0;
    }

    /**
     * Determines whether or not the exception level is loggable
     *
     * @param string $level The PSR-3 log level
     * @return bool True if the level is loggable, otherwise false
     */
    protected function shouldLogException(string $level): bool
    {
        return in_array($level, $this->exceptionLogLevels, true);
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
