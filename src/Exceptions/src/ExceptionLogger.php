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

use Exception;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Defines the logger for exceptions
 */
class ExceptionLogger implements IExceptionLogger
{
    /** @const The default name to use for the logger */
    private const DEFAULT_LOGGER_NAME = 'app';
    /** @var LoggerInterface The logger */
    protected LoggerInterface $logger;
    /** @var ExceptionLogLevelFactoryRegistry The registry of exception log level factories */
    protected ?ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories;
    /** @var array The PSR-3 exception log level that will be logged */
    protected ?array $exceptionLogLevels;
    /** @var int The bitwise value of error levels that are to be logged */
    protected int $errorLogLevels;

    /**
     * @param LoggerInterface|null $logger The logger to use, or null if using the default error logger
     * @param ExceptionLogLevelFactoryRegistry|null $exceptionLogLevelFactories The registry of exception log level factories
     * @param array|null $exceptionLogLevels The PSR-3 exception log levels that will be logged, or null if
     *      using the default levels
     * @param int $errorLogLevels The bitwise value of error levels that are to be logged
     */
    public function __construct(
        LoggerInterface $logger = null,
        ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories = null,
        array $exceptionLogLevels = null,
        int $errorLogLevels = 0
    ) {
        $this->logger = $logger ?? new Logger(self::DEFAULT_LOGGER_NAME, [new ErrorLogHandler()]);
        $this->exceptionLogLevelFactories = $exceptionLogLevelFactories ?? new ExceptionLogLevelFactoryRegistry();
        $this->exceptionLogLevels = $exceptionLogLevels ?? [
            LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY
        ];
        $this->errorLogLevels = $errorLogLevels;
    }

    /**
     * @inheritdoc
     */
    public function logError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        array $context = []
    ): void {
        if ($this->shouldLogError($level)) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * @inheritdoc
     */
    public function logException(Exception $ex): void
    {
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
}
