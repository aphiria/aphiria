<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Middleware;

use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\{IHttpRequestMessage, IHttpResponseMessage};

/**
 * Defines the interface for route middleware to implement
 */
interface IMiddleware
{
    /**
     * Handles a request
     *
     * @param IHttpRequestMessage $request The request to handle
     * @param IRequestHandler $next The next request handler in the pipeline
     * @return IHttpResponseMessage The response after the middleware was run
     */
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage;
}
