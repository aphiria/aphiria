<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Handlers\Mocks;

use Closure;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Mocks middleware that sets a header for use in tests
 */
class MiddlewareThatAddsHeader implements IMiddleware
{
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        /** @var IHttpResponseMessage $response */
        $response = $next($request);
        $response->getHeaders()->add('Foo', 'bar');

        return $response;
    }
}
