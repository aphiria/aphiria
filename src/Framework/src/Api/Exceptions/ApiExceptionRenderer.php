<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamResponseWriter;
use Closure;
use Exception;

/**
 * Defines the exception renderer for API applications
 */
class ApiExceptionRenderer implements IApiExceptionRenderer
{
    /** @var IExceptionResponseFactory The exception response factory */
    protected IExceptionResponseFactory $exceptionResponseFactory;
    /** @var IRequest|null The current request, if there is one */
    protected ?IRequest $request;
    /** @var IResponseFactory|null The optional response factory */
    protected ?IResponseFactory $responseFactory;
    /** @var Closure[] The mapping of exception types to closures that return responses */
    protected array $responseFactories = [];
    /** @var IResponseWriter What is used to write the response */
    protected IResponseWriter $responseWriter;

    /**
     * @param IExceptionResponseFactory|null $exceptionResponseFactory The exception response factory, or null if using the default
     * @param IRequest|null $request The current request, if there is one
     * @param IResponseFactory|null $responseFactory The optional response factory
     * @param IResponseWriter|null $responseWriter What is used to write the response
     */
    public function __construct(
        IExceptionResponseFactory $exceptionResponseFactory = null,
        IRequest $request = null,
        IResponseFactory $responseFactory = null,
        IResponseWriter $responseWriter = null
    ) {
        $this->exceptionResponseFactory = $exceptionResponseFactory ?? new ProblemDetailsExceptionResponseFactory();
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
    }

    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponse(Exception $ex): IResponse
    {
        if ($this->request === null || $this->responseFactory === null) {
            return $this->exceptionResponseFactory->createResponseWithoutContext($ex);
        }

        return $this->exceptionResponseFactory->createResponseWithContext($ex, $this->request, $this->responseFactory);
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $ex): void
    {
        $this->responseWriter->writeResponse($this->createResponse($ex));
    }

    /**
     * @inheritdoc
     */
    public function setRequest(IRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function setResponseFactory(IResponseFactory $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }
}
