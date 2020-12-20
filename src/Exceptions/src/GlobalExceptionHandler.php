<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use ErrorException;
use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Defines the global exception handler
 */
class GlobalExceptionHandler implements IGlobalExceptionHandler
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var LoggerInterface The PSR-3 logger */
    protected LoggerInterface $logger;
    /** @var LogLevelFactory The factory for PSR-3 log levels */
    protected LogLevelFactory $logLevelFactory;

    /**
     * @param IExceptionRenderer $exceptionRenderer The underlying exception renderer
     * @param LoggerInterface|null $logger The PSR-3 logger
     * @param LogLevelFactory|null $logLevelFactory The PSR-3 log level factory
     */
    public function __construct(
        protected IExceptionRenderer $exceptionRenderer,
        LoggerInterface $logger = null,
        LogLevelFactory $logLevelFactory = null
    ) {
        $this->logger = $logger  ?? new Logger(self::DEFAULT_LOGGER_NAME, [new ErrorLogHandler()]);
        $this->logLevelFactory = $logLevelFactory ?? new LogLevelFactory();
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
            // We cannot mock Throwable.  So, this is untestable.
            // @codeCoverageIgnoreStart
            $ex = new FatalThrowableError($ex);
            // @codeCoverageIgnoreEnd
        }

        $logLevel = $this->logLevelFactory->createLogLevel($ex);
        $this->logger->{$logLevel}($ex);
        $this->exceptionRenderer->render($ex);
    }

    /**
     * @inheritdoc
     * @param array|null $error The error that was thrown (only used for testing)
     */
    public function handleShutdown(array $error = null): void
    {
        /** @var array{type: int, message: string, file: string, line: int}|null $error */
        $error = $error ?? error_get_last();

        if ($error !== null && \in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
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
        /** @psalm-suppress InvalidArgument The handleError() method accepts the correct params */
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
}
