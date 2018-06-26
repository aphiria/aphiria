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
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Opulence\Api\Handlers\DependencyResolutionException;
use Opulence\Api\RequestContext;
use Opulence\Net\Http\Formatting\ResponseWriter;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Routing\Matchers\RouteNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Defines the exception handler
 */
class ExceptionHandler implements IExceptionHandler
{
    /** @var LoggerInterface The logger */
    protected $logger;
    /** @var ExceptionResponseFactoryRegistry The registry of exception response factories */
    protected $exceptionResponseFactories;
    /** @var ResponseWriter What to use to write a response */
    protected $responseWriter;
    /** @var array The list of exception classes to not log */
    protected $exceptionsNotLogged;
    /** @var RequestContext|null The current request context, or null if there is none */
    protected $requestContext;

    /**
     * @param LoggerInterface|null $logger The logger to use, or null if using the default error logger
     * @param ExceptionResponseFactoryRegistry|null $exceptionResponseFactories The exception response factory registry
     * @param ResponseWriter $responseWriter What to use to write a response
     * @param string|array $exceptionsNotLogged The exception or list of exceptions to not log when thrown
     */
    public function __construct(
        LoggerInterface $logger = null,
        ExceptionResponseFactoryRegistry $exceptionResponseFactories = null,
        ResponseWriter $responseWriter = null,
        $exceptionsNotLogged = []
    ) {
        if ($logger === null) {
            $logger = new Logger('app');
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->logger = $logger;

        if ($exceptionResponseFactories === null) {
            $exceptionResponseFactories = $this->createDefaultExceptionResponseFactoryRegistry();
        }

        $this->exceptionResponseFactories = $exceptionResponseFactories;
        $this->responseWriter = $responseWriter ?? new ResponseWriter();
        $this->exceptionsNotLogged = (array)$exceptionsNotLogged;
    }

    /**
     * @inheritdoc
     */
    public function handle($ex): void
    {
        // It's Throwable, but not an Exception
        if (!$ex instanceof Exception) {
            $ex = new FatalThrowableError($ex);
        }

        if ($this->shouldLog($ex)) {
            $this->logger->error($ex);
        }

        if ($this->requestContext === null) {
            $response = $this->createDefaultInternalServerErrorResponse($ex, null);
        } else {
            $exceptionType = \get_class($ex);

            try {
                if (($responseFactory = $this->exceptionResponseFactories->getFactory($exceptionType)) === null) {
                    $response = (new InternalServerErrorResponseFactory)->createResponse($this->requestContext);
                } else {
                    $response = $responseFactory($ex, $this->requestContext);
                }
            } catch (Exception $ex) {
                // An exception occurred while making the response, eg content negotiation failed
                $response = $this->createDefaultInternalServerErrorResponse($ex, $this->requestContext);
            }
        }

        $this->responseWriter->writeResponse($response);
    }

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        \set_exception_handler([$this, 'handle']);
    }

    /**
     * @inheritdoc
     */
    public function setRequestContext(RequestContext $requestContext): void
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Determines whether or not an exception should be logged
     *
     * @param Throwable|Exception $ex The exception to check
     * @return bool True if the exception should be logged, otherwise false
     */
    protected function shouldLog($ex): bool
    {
        return !\in_array(\get_class($ex), $this->exceptionsNotLogged);
    }

    /**
     * Creates the default exception response factory registry if none was specified
     *
     * @return ExceptionResponseFactoryRegistry The default response factory registry
     */
    protected function createDefaultExceptionResponseFactoryRegistry(): ExceptionResponseFactoryRegistry
    {
        $responseFactoryRegistry = new ExceptionResponseFactoryRegistry();
        $responseFactoryRegistry->registerFactory(
            HttpException::class,
            function (HttpException $ex, RequestContext $requestContext) {
                return $ex->getResponse();
            }
        );
        $responseFactoryRegistry->registerFactory(
            RouteNotFoundException::class,
            function (RouteNotFoundException $ex, RequestContext $requestContext) {
                return (new NotFoundResponseFactory)->createResponse($requestContext);
            }
        );
        $responseFactoryRegistry->registerFactory(
            DependencyResolutionException::class,
            function (DependencyResolutionException $ex, RequestContext $requestContext) {
                return (new InternalServerErrorResponseFactory)->createResponse($requestContext);
            }
        );

        return $responseFactoryRegistry;
    }

    /**
     * Creates the default internal server error response in the case that content negotiation failed
     *
     * @param Exception $ex The exception that was thrown
     * @param RequestContext|null $requestContext The current request context if there is one, otherwise null
     * @return IHttpResponseMessage The default response
     */
    protected function createDefaultInternalServerErrorResponse(
        Exception $ex,
        ?RequestContext $requestContext
    ): IHttpResponseMessage {
        // We purposely aren't using the parameters - they're more for derived classes that might override this method
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json');

        return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $headers);
    }
}
