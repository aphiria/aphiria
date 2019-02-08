<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Exceptions;

use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;

/**
 * Defines the interface for exception response factories to implement
 */
interface IExceptionResponseFactory
{
    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception to create a response from
     * @param IHttpRequestMessage|null $request The current request, if there is one, otherwise null
     * @return IHttpResponseMessage The response
     */
    public function createResponseFromException(
        Exception $ex,
        ?IHttpRequestMessage $request
    ): IHttpResponseMessage;
}
