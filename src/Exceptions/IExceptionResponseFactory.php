<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Exceptions;

use Exception;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

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
