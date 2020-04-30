<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Closure;
use Exception;

/**
 * Defines the exception renderer for API applications
 */
interface IApiExceptionRenderer extends IExceptionRenderer
{
    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponse(Exception $ex): IResponse;

    /**
     * Registers many factories for exceptions
     *
     * @param Closure[] $exceptionTypesToFactories The mapping of exception types to factories
     */
    public function registerManyResponseFactories(array $exceptionTypesToFactories): void;

    /**
     * Registers a factory for a specific type of exception
     *
     * @param string $exceptionType The type of exception whose factory we're registering
     * @param Closure $factory The factory that takes in an instance of the exception, the request, and the response factory
     */
    public function registerResponseFactory(string $exceptionType, Closure $factory): void;

    /**
     * Sets the current request in case it wasn't initially available
     *
     * @param IRequest $request The current request
     */
    public function setRequest(IRequest $request): void;

    /**
     * Sets the response factory
     *
     * @param IResponseFactory $responseFactory The response factory to set
     */
    public function setResponseFactory(IResponseFactory $responseFactory): void;
}
