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
use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\Http\HttpExceptionHandler;
use Aphiria\Exceptions\IExceptionHandler;
use Aphiria\Exceptions\Logger;
use Aphiria\Exceptions\LogLevelFactoryRegistry;
use Aphiria\Exceptions\ILogger;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

/**
 * Defines the global exception handler bootstrapper
 */
final class GlobalExceptionHandlerBootstrapper implements IBootstrapper
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
     * @throws ConfigurationException Thrown if the configuration was not valid
     */
    public function bootstrap(): void
    {
        // TODO: Need to switch on whether or not we're running in HTTP or console
        $exceptionHandler = $this->createAndBindHttpExceptionHandler();
        $logger = $this->createAndBindLogger();
        $globalExceptionHandler = new GlobalExceptionHandler(
            $exceptionHandler,
            $logger,
            $this->createAndBindLogLevelFactories(),
            GlobalConfiguration::getInt('aphiria.exceptions.errorThrownLevels')
        );
        $globalExceptionHandler->registerWithPhp();
        $this->container->bindInstance(GlobalExceptionHandler::class, $globalExceptionHandler);
    }

    /**
     * Creates and binds an exception log level factory registry
     *
     * @return LogLevelFactoryRegistry The created exception log level factories
     */
    protected function createAndBindLogLevelFactories(): LogLevelFactoryRegistry
    {
        $exceptionLogLevelFactories = new LogLevelFactoryRegistry();
        $this->container->bindInstance(LogLevelFactoryRegistry::class, $exceptionLogLevelFactories);

        return $exceptionLogLevelFactories;
    }

    /**
     * Creates and binds the exception handler for HTTP applications
     *
     * @return IExceptionHandler The exception handler for HTTP applications
     * @throws ConfigurationException Thrown if configuration values were invalid or missing
     */
    protected function createAndBindHttpExceptionHandler(): IExceptionHandler
    {
        $useProblemDetails = GlobalConfiguration::getBool('aphiria.exceptions.useProblemDetails');
        $exceptionHandler = new HttpExceptionHandler($useProblemDetails);
        $exceptionHandler->registerManyNegotiatedResponseFactories([
            HttpException::class => function (HttpException $ex, IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory) {
                return $ex->getResponse();
            },
            InvalidRequestBodyException::class => function (InvalidRequestBodyException $ex, IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory) use ($useProblemDetails) {
                if ($useProblemDetails) {
                    $body = new ValidationProblemDetails($ex->getErrors());
                    $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_BAD_REQUEST, null, $body);

                    return (new ProblemDetailsResponseMutator)->mutateResponse($response);
                }

                return new Response(HttpStatusCodes::HTTP_BAD_REQUEST);
            }
        ]);
        $this->container->bindInstance([IExceptionHandler::class, HttpExceptionHandler::class], $exceptionHandler);

        return $exceptionHandler;
    }

    /**
     * Creates and binds a PSR-3 logger instance to use in the exception handler
     *
     * @return LoggerInterface The PSR-3 logger to use
     * @throws ConfigurationException Thrown if the configuration was invalid
     */
    protected function createAndBindLogger(): LoggerInterface
    {
        $logger = new MonologLogger(GlobalConfiguration::getString('aphiria.logging.name'));

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
                    throw new ConfigurationException("Unsupported logging handler type {$handlerConfiguration['type']}");
            }
        }

        $this->container->bindInstance(LoggerInterface::class, $logger);

        return $logger;
    }
}
