<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware;

use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Exception;

/**
 * Defines the interface for route middleware to implement
 */
interface IMiddleware
{
    /**
     * Handles a request
     *
     * @param IRequest $request The request to handle
     * @param IRequestHandler $next The next request handler in the pipeline
     * @return IResponse The response after the middleware was run
     * @throws HttpException Thrown if there was an HTTP exception processing the request
     * @throws Exception Thrown if there was any other type of exception thrown while processing the request
     */
    public function handle(IRequest $request, IRequestHandler $next): IResponse;
}
