<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\Handlers;

use Exception;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;

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
