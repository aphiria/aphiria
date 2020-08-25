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
use Exception;

/**
 * Defines the interface for exception response factories to implement
 */
interface IExceptionResponseFactory
{
    /**
     * Creates a response from an exception, request, and response factory
     *
     * @param Exception $ex The exception that was thrown
     * @param IRequest $request The current request
     * @param IResponseFactory $responseFactory The response factory
     * @return IResponse The response
     */
    public function createResponseWithContext(Exception $ex, IRequest $request, IResponseFactory $responseFactory): IResponse;

    /**
     * Creates a response from an exception without a request or response factory, eg an exception thrown on app startup
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponseWithoutContext(Exception $ex): IResponse;
}
