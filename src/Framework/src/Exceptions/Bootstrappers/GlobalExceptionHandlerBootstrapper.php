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

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\ValidationProblemDetails;
use Aphiria\Application\IBootstrapper;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Exceptions\ExceptionLogger;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactory;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionLogger;
use Aphiria\Exceptions\IExceptionResponseFactory;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Closure;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Defines the global exception handler bootstrapper
 */
final class GlobalExceptionHandlerBootstrapper implements IBootstrapper
{
    /** @var IContainer The DI container */
    private IContainer $container;

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
     * @throws ResolutionException Thrown if dependencies could not be resolved
     */
    public function bootstrap(): void
    {
        $exceptionResponseFactoryRegistry = new ExceptionResponseFactoryRegistry();
        $exceptionResponseFactoryRegistry->registerDefaultFactory($this->getDefaultExceptionResponseFactory($this->container));
        $exceptionResponseFactoryRegistry->registerManyFactories([
            HttpException::class => fn(HttpException $ex, IHttpRequestMessage $request) => $ex->getResponse(),
            InvalidRequestBodyException::class => $this->getInvalidRequestBodyResponseFactory()
        ]);
        $exceptionResponseFactory = new ExceptionResponseFactory(
            $this->container->resolve(INegotiatedResponseFactory::class),
            $exceptionResponseFactoryRegistry
        );
        $this->container->bindInstance(ExceptionResponseFactoryRegistry::class, $exceptionResponseFactoryRegistry);
        $this->container->bindInstance(IExceptionResponseFactory::class, $exceptionResponseFactory);

        $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry();
        $this->container->bindInstance(ExceptionLogLevelFactoryRegistry::class, $exceptionLogLevelFactories);

        $psr3Logger = $this->getPsr3Logger();
        $exceptionLogger = new ExceptionLogger(
            $psr3Logger,
            $exceptionLogLevelFactories,
            GlobalConfiguration::getArray('aphiria.exceptions.exceptionLogLevels'),
            GlobalConfiguration::getInt('aphiria.exceptions.errorLogLevels')
        );
        $this->container->bindInstance(LoggerInterface::class, $psr3Logger);
        $this->container->bindInstance(IExceptionLogger::class, $exceptionLogger);

        $globalExceptionHandler = new GlobalExceptionHandler(
            $exceptionResponseFactory,
            $exceptionLogger,
            GlobalConfiguration::getInt('aphiria.exceptions.errorThrownLevels')
        );
        $globalExceptionHandler->registerWithPhp();
        $this->container->bindInstance(GlobalExceptionHandler::class, $globalExceptionHandler);
    }

    /**
     * Gets the default exception response factory to use
     *
     * @param IContainer $container The DI container
     * @return Closure The exception response factory
     * @throws ConfigurationException Thrown if the config is missing values
     */
    private function getDefaultExceptionResponseFactory(IContainer $container): Closure
    {
        if (!GlobalConfiguration::getBool('aphiria.exceptions.useProblemDetails')) {
            return static function (
                Exception $ex,
                ?IHttpRequestMessage $request,
                INegotiatedResponseFactory $responseFactory
            ): IHttpResponseMessage {
                return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
            };
        }

        return static function (
            Exception $ex,
            ?IHttpRequestMessage $request,
            INegotiatedResponseFactory $responseFactory
        ) use (
            $container
        ): IHttpResponseMessage {
            $problemDetails = new ProblemDetails(
                'https://tools.ietf.org/html/rfc7231#section-6.6.1',
                'An error occurred',
                null,
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
            );

            if ($request === null) {
                /**
                 * If the request hasn't even been initialized, the exception must've happened very early in the
                 * application pipeline.  So, just default to a JSON response.
                 */
                $response = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
                $response->getHeaders()->add('Content-Type', 'application/problem+json');
                $bodyStream = new Stream(fopen('php://temp', 'r+b'));
                /** @var JsonMediaTypeFormatter $mediaTypeFormatter */
                $mediaTypeFormatter = $container->resolve(JsonMediaTypeFormatter::class);
                $mediaTypeFormatter->writeToStream($problemDetails, $bodyStream, null);
                $response->setBody(new StreamBody($bodyStream));

                return $response;
            }

            // Since we have a request, let's use content negotiation to create the response
            $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, null,
                $problemDetails);

            return (new ProblemDetailsResponseMutator)->mutateResponse($response);
        };
    }

    /**
     * Gets the response factory for invalid request body exceptions
     *
     * @return Closure The response factory
     * @throws ConfigurationException Thrown if the config is missing values
     */
    private function getInvalidRequestBodyResponseFactory(): Closure
    {
        if (!GlobalConfiguration::getBool('aphiria.exceptions.useProblemDetails')) {
            return static function (
                InvalidRequestBodyException $ex,
                IHttpRequestMessage $request,
                INegotiatedResponseFactory $responseFactory
            ): IHttpResponseMessage {
                return new Response(HttpStatusCodes::HTTP_BAD_REQUEST);
            };
        }

        return static function (
            InvalidRequestBodyException $ex,
            IHttpRequestMessage $request,
            INegotiatedResponseFactory $responseFactory
        ): IHttpResponseMessage {
            $body = new ValidationProblemDetails($ex->getErrors());
            $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_BAD_REQUEST, null, $body);

            return (new ProblemDetailsResponseMutator)->mutateResponse($response);
        };
    }

    /**
     * Gets the PSR-3 logger instance to use in the exception handler
     *
     * @return LoggerInterface The PSR-3 logger to use
     * @throws ConfigurationException Thrown if the configuration was invalid
     */
    private function getPsr3Logger(): LoggerInterface
    {
        $logger = new Logger(GlobalConfiguration::getString('aphiria.logging.name'));

        foreach (GlobalConfiguration::getArray('aphiria.logging.handlers') as $handlerConfiguration) {
            switch ($handlerConfiguration['type']) {
                case StreamHandler::class:
                    $logger->pushHandler(new StreamHandler($handlerConfiguration['path']));
                    break;
                case SyslogHandler::class:
                    $logger->pushHandler(new SyslogHandler($handlerConfiguration['ident'] ?? 'app'));
                    break;
                default:
                    throw new ConfigurationException("Unsupported logging handler type {$handlerConfiguration['type']}");
            }
        }

        return $logger;
    }
}
