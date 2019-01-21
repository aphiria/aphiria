<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Mocks;

use Opulence\Middleware\IMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Mocks middleware that increments a header value for use in tests
 */
class MiddlewareThatIncrementsHeader implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        /** @var IHttpResponseMessage $response */
        $response = $next->handle($request);
        $currValues = [];

        // Keep appending an incrementing value to a header
        if ($response->getHeaders()->tryGet('Foo', $currValues)) {
            $response->getHeaders()->add('Foo', $currValues[\count($currValues) - 1] + 1, true);
        } else {
            $response->getHeaders()->add('Foo', 1);
        }

        return $response;
    }
}
