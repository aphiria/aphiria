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

use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use Closure;
use Exception;
use Throwable;

/**
 * Defines the exception response factory registry
 */
final class ExceptionResponseFactoryRegistry
{
    /** @var Closure[] The mapping of exception types to response factories */
    private array $factories = [];
    /** @var Closure The default factory */
    private Closure $defaultFactory;

    public function __construct()
    {
        // Default to a basic factory.  This factory MUST NOT throw an exception.
        $this->defaultFactory = function (Exception $ex, ?IHttpRequestMessage $request, INegotiatedResponseFactory $negotiatedResponseFactory): IHttpResponseMessage {
            if ($request === null) {
                // There's no context as to what content type is preferred, so just default to JSON
                return $this->createDefaultResponse();
            }

            try {
                return $negotiatedResponseFactory->createResponse($request, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
            } catch (Exception | Throwable $ex) {
                return $this->createDefaultResponse();
            }
        };
    }

    /**
     * Gets the default response factory
     *
     * @return Closure The default factory
     */
    public function getDefaultFactory(): Closure
    {
        return $this->defaultFactory;
    }

    /**
     * Gets the factory for a particular exception
     *
     * @param string $exceptionType The type of exception whose factory we want
     * @return Closure|null The response factory if one was found, otherwise null
     */
    public function getFactory(string $exceptionType): ?Closure
    {
        if (!isset($this->factories[$exceptionType])) {
            return null;
        }

        return $this->factories[$exceptionType];
    }

    /**
     * Registers the default factory to use if no factory is explicitly registered for an exception type
     * Note: This factory MUST NOT throw an exception
     *
     * @param Closure $responseFactory The default factory that takes in an exception instance, nullable request, and a negotiated response factory
     */
    public function registerDefaultFactory(Closure $responseFactory): void
    {
        $this->defaultFactory = $responseFactory;
    }

    /**
     * Registers a response factory for an exception type
     *
     * @param string $exceptionType The type the response factory applies to
     * @param Closure $responseFactory The response factory that takes in an exception instance, nullable request, and a negotiated response factory
     */
    public function registerFactory(string $exceptionType, Closure $responseFactory): void
    {
        $this->factories[$exceptionType] = $responseFactory;
    }

    /**
     * Registers a response factory for an exception type
     *
     * @param Closure[] $exceptionTypesToFactories The exception types to factories
     */
    public function registerManyFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $responseFactory) {
            $this->registerFactory($exceptionType, $responseFactory);
        }
    }

    /**
     * Creates a default response when a response cannot otherwise be created
     *
     * @return IHttpResponseMessage The response
     */
    private function createDefaultResponse(): IHttpResponseMessage
    {
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json');

        return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $headers);
    }
}
