<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;

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
