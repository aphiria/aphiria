<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Handlers;

use Exception;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for request handlers to implement
 */
interface IRequestHandler
{
    /**
     * Handles a request and returns a response
     *
     * @param IHttpRequestMessage $request The incoming request
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an HTTP exception processing the request
     * @throws Exception Thrown if there was any other type of exception thrown while processing the request
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage;
}
