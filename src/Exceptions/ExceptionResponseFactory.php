<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Exceptions;

use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Response;
use Exception;
use function get_class;

/**
 * Defines a factory for responses created from exceptions
 */
class ExceptionResponseFactory implements IExceptionResponseFactory
{
    /** @var INegotiatedResponseFactory The negotiated response factory */
    protected $negotiatedResponseFactory;
    /** @var ExceptionResponseFactoryRegistry The registry of exception response factories */
    protected $exceptionResponseFactories;

    /**
     * @param INegotiatedResponseFactory $negotiatedResponseFactory
     * @param ExceptionResponseFactoryRegistry|null $exceptionResponseFactories The exception response factory registry
     */
    public function __construct(
        INegotiatedResponseFactory $negotiatedResponseFactory,
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
