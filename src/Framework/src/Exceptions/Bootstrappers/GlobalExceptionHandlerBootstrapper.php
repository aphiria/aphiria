<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Bootstrappers;

use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\Application\IBootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Exceptions\IGlobalExceptionHandler;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Defines the global exception handler bootstrapper
 */
class GlobalExceptionHandlerBootstrapper implements IBootstrapper
{
    /**
     * @param IContainer $container The DI container
     */
    public function __construct(protected IContainer $container)
    {
    }

    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config was missing values
     * @throws ResolutionException Thrown if any dependencies could not be resolved
     */
    public function bootstrap(): void
    {
        $apiExceptionRenderer = $this->createAndBindApiExceptionRenderer();
        $consoleExceptionRenderer = $this->createAndBindConsoleExceptionRenderer();
        $logger = $this->createAndBindLogger();
        $logLevelFactory = $this->createAndBindLogLevelFactory();

        if ($this->isRunningInConsole()) {
            $this->container->bindInstance(IExceptionRenderer::class, $consoleExceptionRenderer);
            $globalExceptionHandler = new GlobalExceptionHandler($consoleExceptionRenderer, $logger, $logLevelFactory);
        } else {
            $this->container->bindInstance(IExceptionRenderer::class, $apiExceptionRenderer);
            $globalExceptionHandler = new GlobalExceptionHandler($apiExceptionRenderer, $logger, $logLevelFactory);
        }

        $globalExceptionHandler->registerWithPhp();
        $this->container->bindInstance(
            [IGlobalExceptionHandler::class, GlobalExceptionHandler::class],
            $globalExceptionHandler
        );
    }

    /**
     * Creates and binds the exception renderer for API applications
     *
     * @return IExceptionRenderer The exception renderer for API applications
     * @throws MissingConfigurationValueException Thrown if configuration values were invalid or missing
     * @throws ResolutionException Thrown if a custom exception renderer could not be resolved
     */
    protected function createAndBindApiExceptionRenderer(): IExceptionRenderer
    {
        $exceptionRendererType = GlobalConfiguration::getString('aphiria.exceptions.apiExceptionRenderer');

        switch ($exceptionRendererType) {
            case ProblemDetailsExceptionRenderer::class:
                $exceptionRenderer = new ProblemDetailsExceptionRenderer();
                $exceptionRenderer->mapExceptionToProblemDetails(
                    HttpException::class,
                    status: fn (HttpException $ex) => $ex->getResponse()->getStatusCode()
                );
                $exceptionRenderer->mapExceptionToProblemDetails(
                    InvalidRequestBodyException::class,
                    status: HttpStatusCodes::BAD_REQUEST
                );
                break;
            default:
                $exceptionRenderer = $this->container->resolve($exceptionRendererType);
                break;
        }

        // We'll bind IExceptionRenderer in the calling method
        $this->container->bindInstance([IApiExceptionRenderer::class, $exceptionRendererType], $exceptionRenderer);

        return $exceptionRenderer;
    }

    /**
     * Creates and binds the exception renderer for console applications
     *
     * @return IExceptionRenderer The exception renderer for console applications
     */
    protected function createAndBindConsoleExceptionRenderer(): IExceptionRenderer
    {
        $exceptionRenderer = new ConsoleExceptionRenderer();
        // We'll bind to the interface in the calling method
        $this->container->bindInstance(ConsoleExceptionRenderer::class, $exceptionRenderer);

        return $exceptionRenderer;
    }

    /**
     * Creates and binds a PSR-3 logger instance to use in the exception handler
     *
     * @return LoggerInterface The PSR-3 logger to use
     * @throws InvalidArgumentException Thrown if the configuration was invalid
     * @throws MissingConfigurationValueException Thrown if configuration values were invalid or missing
     */
    protected function createAndBindLogger(): LoggerInterface
    {
        $logger = new Logger(GlobalConfiguration::getString('aphiria.logging.name'));

        foreach (GlobalConfiguration::getArray('aphiria.logging.handlers') as $handlerConfiguration) {
            switch ($handlerConfiguration['type']) {
                case StreamHandler::class:
                    $logger->pushHandler(new StreamHandler(
                        $handlerConfiguration['path'],
                        $handlerConfiguration['level']
                    ));
                    break;
                case SyslogHandler::class:
                    $logger->pushHandler(new SyslogHandler(
                        $handlerConfiguration['ident'] ?? 'app',
                        LOG_USER,
                        $handlerConfiguration['level']
                    ));
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported logging handler type {$handlerConfiguration['type']}");
            }
        }

        $this->container->bindInstance(LoggerInterface::class, $logger);

        return $logger;
    }

    /**
     * Creates and binds a log level factory for use in the exception handler
     *
     * @return LogLevelFactory The bound log level factory
     */
    protected function createAndBindLogLevelFactory(): LogLevelFactory
    {
        $logLevelFactory = new LogLevelFactory();
        $this->container->bindInstance(LogLevelFactory::class, $logLevelFactory);

        return $logLevelFactory;
    }

    /**
     * Gets whether or not the app is running in the console
     *
     * @return bool True if the application is running in the console, otherwise false
     */
    protected function isRunningInConsole(): bool
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
}
