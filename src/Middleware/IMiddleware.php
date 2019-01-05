<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Middleware;

use Closure;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for route middleware to implement
 */
interface IMiddleware
{
    /**
     * Handles a request
     *
     * @param IHttpRequestMessage $request The request to handle
     * @param Closure $next The next middleware item
     * @return IHttpResponseMessage The response after the middleware was run
     */
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage;
}
