<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
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
