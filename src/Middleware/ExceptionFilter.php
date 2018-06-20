<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Middleware;

use Exception;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Throwable;

/**
 * Defines the exception filter middleware
 */
class ExceptionFilter implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        // Todo: Probably makes sense to include exception/error handler classes that I'd inject into this middleware
        // Todo: These handlers would be configurable, and would take out most of the logic from this middleware
        try {
            return $next($request);
        } catch (Exception | Throwable $ex) {
            // Todo: Handle/log this exception
            // Todo: How do I handle HttpException?
        }
    }
}
