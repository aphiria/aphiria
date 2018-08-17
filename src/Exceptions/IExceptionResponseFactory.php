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
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\RequestContext;

/**
 * Defines the interface for exception response factories to implement
 */
interface IExceptionResponseFactory
{
    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception to create a response from
     * @param RequestContext|null $requestContext The current request context, if there is one, otherwise null
     * @return IHttpResponseMessage The response
     */
    public function createResponseFromException(
        Exception $ex,
        ?RequestContext $requestContext
    ): IHttpResponseMessage;
}
