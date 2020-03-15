<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use Throwable;

/**
 * Defines a factory for responses created from exceptions
 */
class ExceptionResponseFactory implements IExceptionResponseFactory
{
    /** @var INegotiatedResponseFactory The negotiated response factory */
    protected INegotiatedResponseFactory $negotiatedResponseFactory;
    /** @var ExceptionResponseFactoryRegistry The registry of exception response factories */
    protected ?ExceptionResponseFactoryRegistry $exceptionResponseFactories;

    /**
     * @param INegotiatedResponseFactory|null $negotiatedResponseFactory The factory that creates negotiated responses, otherwise the default factory
     * @param ExceptionResponseFactoryRegistry|null $exceptionResponseFactories The exception response factory registry
     */
    public function __construct(
        INegotiatedResponseFactory $negotiatedResponseFactory = null,
        ExceptionResponseFactoryRegistry $exceptionResponseFactories = null
    ) {
        $this->negotiatedResponseFactory = $negotiatedResponseFactory ?? new NegotiatedResponseFactory();
        $this->exceptionResponseFactories = $exceptionResponseFactories ?? $this->createDefaultExceptionResponseFactories();
    }

    /**
     * @inheritdoc
     */
    public function createResponseFromException(
        Exception $ex,
        ?IHttpRequestMessage $request
    ): IHttpResponseMessage {
        $exceptionType = get_class($ex);

        if ($request === null || ($responseFactory = $this->exceptionResponseFactories->getFactory($exceptionType)) === null) {
            return $this->invokeDefaultFactory($ex, $request);
        }

        try {
            return $responseFactory($ex, $request, $this->negotiatedResponseFactory);
        } catch (Exception | Throwable $ex) {
            // An exception occurred while making the response, eg content negotiation failed
            return $this->invokeDefaultFactory($ex, $request);
        }
    }

    /**
     * Creates the default exception response factory registry if none was specified
     *
     * @return ExceptionResponseFactoryRegistry The default response factory registry
     */
    protected function createDefaultExceptionResponseFactories(): ExceptionResponseFactoryRegistry
    {
        $responseFactories = new ExceptionResponseFactoryRegistry();
        $responseFactories->registerFactory(
            HttpException::class,
            fn (HttpException $ex, ?IHttpRequestMessage $request) => $ex->getResponse()
        );

        return $responseFactories;
    }

    /**
     * Invokes the default factory
     *
     * @param Exception $ex The exception that was thrown
     * @param IHttpRequestMessage|null $request The optional request
     * @return IHttpResponseMessage The response
     */
    private function invokeDefaultFactory(Exception $ex, ?IHttpRequestMessage $request): IHttpResponseMessage
    {
        return ($this->exceptionResponseFactories->getDefaultFactory())($ex, $request, $this->negotiatedResponseFactory);
    }
}
