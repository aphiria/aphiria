<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Bootstrappers;

use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\ValidationProblemDetails;
use Aphiria\Application\IBootstrapper;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Exceptions\IGlobalExceptionHandler;
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
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
    /** @var IContainer The DI container */
    protected IContainer $container;

    /**
     * @param IContainer $container The DI container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config was missing values
     */
    public function bootstrap(): void
    {
        $apiExceptionRenderer = $this->createAndBindApiExceptionRenderer();
        $consoleExceptionRenderer = $this->createAndBindConsoleExceptionRenderer();
        $logger = $this->createAndBindLogger();

        if ($this->isRunningInConsole()) {
            $this->container->bindInstance(IExceptionRenderer::class, $consoleExceptionRenderer);
            $globalExceptionHandler = new GlobalExceptionHandler($consoleExceptionRenderer, $logger);
        } else {
            $this->container->bindInstance(IExceptionRenderer::class, $apiExceptionRenderer);
            $globalExceptionHandler = new GlobalExceptionHandler($apiExceptionRenderer, $logger);
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
     */
    protected function createAndBindApiExceptionRenderer(): IExceptionRenderer
    {
        $useProblemDetails = GlobalConfiguration::getBool('aphiria.exceptions.useProblemDetails');
        $exceptionRenderer = new ApiExceptionRenderer($useProblemDetails);
        $exceptionRenderer->registerManyResponseFactories([
            HttpException::class => function (HttpException $ex, IHttpRequestMessage $request, IResponseFactory $responseFactory) {
                return $ex->getResponse();
            },
            InvalidRequestBodyException::class => function (InvalidRequestBodyException $ex, IHttpRequestMessage $request, IResponseFactory $responseFactory) use ($useProblemDetails) {
                if ($useProblemDetails) {
                    $body = new ValidationProblemDetails($ex->getErrors());
                    $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_BAD_REQUEST, null, $body);

                    return (new ProblemDetailsResponseMutator())->mutateResponse($response);
                }

                return new Response(HttpStatusCodes::HTTP_BAD_REQUEST);
            }
        ]);
        // We'll bind to the interface in the calling method
        $this->container->bindInstance(ApiExceptionRenderer::class, $exceptionRenderer);

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
     * Gets whether or not the app is running in the console
     *
     * @return bool True if the application is running in the console, otherwise false
     */
    protected function isRunningInConsole(): bool
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }
}
