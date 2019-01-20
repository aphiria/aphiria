<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Middleware;

use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the middleware request handler
 */
class MiddlewareRequestHandler implements IRequestHandler
{
    /** @var IMiddleware The middleware that will handle the request */
    private $middleware;
    /** @var IRequestHandler The next request handler */
    private $next;

    /**
     * @param IMiddleware $middleware The middleware that will handle the request
     * @param IRequestHandler $next The next request handler
     */
    public function __construct(IMiddleware $middleware, IRequestHandler $next)
    {
        $this->middleware = $middleware;
        $this->next = $next;
    }

    /**
     * @inheritdoc
     */
    public function handle(IHttpRequestMessage $request): IHttpResponseMessage
    {
        return $this->middleware->handle($request, $this->next);
    }
}