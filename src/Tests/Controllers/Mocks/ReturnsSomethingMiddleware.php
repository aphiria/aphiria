<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Controllers\Mocks;

use Opulence\Api\Middleware\IMiddleware;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\StringBody;

/**
 * Mocks middleware that returns something
 */
class ReturnsSomethingMiddleware implements IMiddleware
{
    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request, IRequestHandler $next): IHttpResponseMessage
    {
        /** @var IHttpResponseMessage $response */
        $response = $next->handle($request);
        $response->setBody(new StringBody("{$response->getBody()}:something"));

        return $response;
    }
}
