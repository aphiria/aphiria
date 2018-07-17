<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Handlers;

use Exception;
use Opulence\Api\RequestContext;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for route action invokers to implement
 */
interface IRouteActionInvoker
{
    /**
     * Invokes a route action
     *
     * @param callable $routeAction The route action callable to invoke
     * @param RequestContext $requestContext The current request context
     * @return IHttpResponseMessage The response
     * @throws Exception Thrown if there was any error processing the request
     */
    public function invokeRouteAction(callable $routeAction, RequestContext $requestContext): IHttpResponseMessage;
}
