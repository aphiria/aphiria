<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Exception;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;

/**
 * Defines a factory for responses created from exceptions
 */
class ExceptionResponseFactory implements IExceptionResponseFactory
{
    /** @var NegotiatedResponseFactory The negotiated response factory */
    protected $negotiatedResponseFactory;
    /** @var ExceptionResponseFactoryRegistry The registry of exception response factories */
    protected $exceptionResponseFactories;

    /**
     * @param NegotiatedResponseFactory $negotiatedResponseFactory
     * @param ExceptionResponseFactoryRegistry|null $exceptionResponseFactories The exception response factory registry
     */
    public function __construct(
        NegotiatedResponseFactory $negotiatedResponseFactory,
        ExceptionResponseFactoryRegistry $exceptionResponseFactories = null
    ) {
        $this->negotiatedResponseFactory = $negotiatedResponseFactory;
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

        $exceptionType = \get_class($ex);

        try {
            if (($responseFactory = $this->exceptionResponseFactories->getFactory($exceptionType)) === null) {
                return $this->negotiatedResponseFactory->createResponse(
                    $request,
                    HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                    null,
                    null
                );
            }

            return $responseFactory($ex, $request);
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
            function (HttpException $ex, ?IHttpRequestMessage $request) {
                return $ex->getResponse();
            }
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
