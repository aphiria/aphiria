<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Middleware;

use Aphiria\Exceptions\ExceptionLogger;
use Aphiria\Exceptions\ExceptionResponseFactory;
use Aphiria\Exceptions\FatalThrowableError;
use Aphiria\Exceptions\IExceptionLogger;
use Aphiria\Exceptions\IExceptionResponseFactory;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use Throwable;

/**
 * Defines the middleware that handles exceptions
 */
final class ExceptionHandler implements IMiddleware
{
    /** @var IExceptionResponseFactory The factory that create exception responses */
    private IExceptionResponseFactory $exceptionResponseFactory;
    /** @var IExceptionLogger The exception logger */
    private IExceptionLogger $logger;

    /**
     * @param IExceptionResponseFactory|null $exceptionResponseFactory The factory that create exception responses, or null if using the default factory
     * @param IExceptionLogger|null $logger The exception logger
     */
    public function __construct(
        IExceptionResponseFactory $exceptionResponseFactory = null,
        IExceptionLogger $logger = null
    ) {
        $this->exceptionResponseFactory = $exceptionResponseFactory ?? new ExceptionResponseFactory();
        $this->logger = $logger ?? new ExceptionLogger();
    }

    /**
     * Handles a request
     *
     * @param IHttpRequestMessage $request The request to handle
     * @param IRequestHandler $next The next request handler in the pipeline
     * @return IHttpResponseMessage The response after the middleware was run
     */
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        try {
            return $next->handle($request);
        } catch (Throwable $ex) {
            if (!$ex instanceof Exception) {
                $ex = new FatalThrowableError($ex);
            }

            $this->logger->logException($ex);

            return $this->exceptionResponseFactory->createResponseFromException($ex, $request);
        }
    }
}
