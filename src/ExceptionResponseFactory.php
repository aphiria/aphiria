<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/exceptions/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use Exception;

/**
 * Defines a factory for responses created from exceptions
 */
final class ExceptionResponseFactory implements IExceptionResponseFactory
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
        if ($request === null) {
            return $this->createDefaultInternalServerErrorResponse($ex, null);
        }

        $exceptionType = get_class($ex);

        try {
            if (($responseFactory = $this->exceptionResponseFactories->getFactory($exceptionType)) === null) {
                return $this->negotiatedResponseFactory->createResponse(
                    $request,
                    HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                    null,
                    null
                );
            }

            return $responseFactory($ex, $request, $this->negotiatedResponseFactory);
        } catch (Exception $ex) {
            // An exception occurred while making the response, eg content negotiation failed
            return $this->createDefaultInternalServerErrorResponse($ex, $request);
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
     * Creates the default internal server error response in the case that content negotiation failed
     *
     * @param Exception $ex The exception that was thrown
     * @param IHttpRequestMessage|null $request The current request if there is one, otherwise null
     * @return IHttpResponseMessage The default response
     */
    protected function createDefaultInternalServerErrorResponse(
        Exception $ex,
        ?IHttpRequestMessage $request
    ): IHttpResponseMessage {
        // We purposely aren't using the parameters - they're more for derived classes that might override this method
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json');

        return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $headers);
    }
}
