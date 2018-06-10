<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Dispatchers;

use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for request dispatchers to implement
 */
interface IRequestDispatcher
{
    /**
     * Dispatches a request to a controller
     *
     * @param IHttpRequestMessage $request The incoming request
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an exception processing the request
     */
    public function dispatchRequest(IHttpRequestMessage $request): IHttpResponseMessage;
}
