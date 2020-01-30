<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Exceptions\Bootstrappers;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\ValidationProblemDetails;
use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
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
use Aphiria\Net\Http\StreamResponseWriter;
use Closure;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Defines the exception handler bootstrapper
 */
final class ExceptionHandlerBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the the config is missing values
     */
    public function registerBindings(IContainer $container): void
    {
        /**
         * ----------------------------------------------------------
         * Create the exception response factory
         * ----------------------------------------------------------
         *
         * Register exception response factories for common exceptions
         */
        $exceptionResponseFactoryRegistry = new ExceptionResponseFactoryRegistry();

        // This is the default response factory for exceptions that do not have custom responses
        $exceptionResponseFactoryRegistry->registerDefaultFactory($this->getDefaultExceptionResponseFactory($container));

        // These are the response factories for exceptions that have custom responses
        $exceptionResponseFactoryRegistry->registerManyFactories([
            HttpException::class => fn (HttpException $ex, IHttpRequestMessage $request) => $ex->getResponse(),
            InvalidRequestBodyException::class => $this->getInvalidRequestBodyResponseFactory()
        ]);
        $exceptionResponseFactory = new ExceptionResponseFactory(
            $container->resolve(INegotiatedResponseFactory::class),
            $exceptionResponseFactoryRegistry
        );

        $container->bindInstance(ExceptionResponseFactoryRegistry::class, $exceptionResponseFactoryRegistry);
        $container->bindInstance(IExceptionResponseFactory::class, $exceptionResponseFactory);

        /**
         * ----------------------------------------------------------
         * Custom log levels
         * ----------------------------------------------------------
         *
         * Specify the exception types to log levels for any common exceptions
         */
        $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry();
        $container->bindInstance(ExceptionLogLevelFactoryRegistry::class, $exceptionLogLevelFactories);

        /**
         * ----------------------------------------------------------
         * Logged exception levels
         * ----------------------------------------------------------
         *
         * Specify the PSR-3 exception levels to log
         */
        $exceptionLogLevels = Configuration::getArray('exceptions.exceptionLogLevels');

        /**
         * ----------------------------------------------------------
         * Logged error levels
         * ----------------------------------------------------------
         *
         * Specify the bitwise value of error levels to log
         */
        $errorLogLevels = Configuration::getInt('exceptions.errorLogLevels');

        /**
         * ----------------------------------------------------------
         * Thrown error levels
         * ----------------------------------------------------------
         *
         * Specify the error levels to rethrow as exceptions
         */
        $errorThrownLevels = Configuration::getInt('exceptions.errorThrownLevels');

        /**
         * ----------------------------------------------------------
         * Create the exception logger
         * ----------------------------------------------------------
         */
        $exceptionLogger = new ExceptionLogger(
            $container->resolve(LoggerInterface::class),
            $exceptionLogLevelFactories,
            $exceptionLogLevels,
            $errorLogLevels
        );
        $container->bindInstance(IExceptionLogger::class, $exceptionLogger);

        /**
         * ----------------------------------------------------------
         * Create the global exception handler
         * ----------------------------------------------------------
         */
        $globalExceptionHandler = new GlobalExceptionHandler(
            $exceptionResponseFactory,
            $exceptionLogger,
            $errorThrownLevels,
            new StreamResponseWriter()
        );
        $container->bindInstance(GlobalExceptionHandler::class, $globalExceptionHandler);
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
        if (!Configuration::getBool('exceptions.useProblemDetails')) {
            return function (Exception $ex, ?IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory): IHttpResponseMessage {
                return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
            };
        }

        return function (Exception $ex, ?IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory) use ($container): IHttpResponseMessage {
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
            $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, null, $problemDetails);

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
        if (!Configuration::getBool('exceptions.useProblemDetails')) {
            return function (InvalidRequestBodyException $ex, IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory): IHttpResponseMessage {
                return new Response(HttpStatusCodes::HTTP_BAD_REQUEST);
            };
        }

        return function (InvalidRequestBodyException $ex, IHttpRequestMessage $request, INegotiatedResponseFactory $responseFactory): IHttpResponseMessage {
            $body = new ValidationProblemDetails($ex->getErrors());
            $response = $responseFactory->createResponse($request, HttpStatusCodes::HTTP_BAD_REQUEST, null, $body);

            return (new ProblemDetailsResponseMutator)->mutateResponse($response);
        };
    }
}
