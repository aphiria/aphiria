<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Dispatchers\Mocks;

use Closure;
use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Mocks a middleware that writes to the response's headers
 */
class HeaderSetter implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request, Closure $next): IHttpResponseMessage
    {
        /** @var IHttpResponseMessage $response */
        $response = $next($request);
        $response->getHeaders()->add('foo', 'bar');

        return $response;
    }
}
