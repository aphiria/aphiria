<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Handlers;

use Exception;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Routing\Matchers\RouteMatchingResult;

/**
 * Defines the interface for route action invokers to implement
 */
interface IRouteActionInvoker
{
    /**
     * Invokes a route action
     *
     * @param callable $routeAction The route action callable to invoke
     * @param IHttpRequestMessage $request The current request
     * @param RouteMatchingResult $matchingResult The result of the route matching
     * @return IHttpResponseMessage The response
     * @throws Exception Thrown if there was any error processing the request
     */
    public function invokeRouteAction(
        callable $routeAction,
        IHttpRequestMessage $request,
        RouteMatchingResult $matchingResult
    ): IHttpResponseMessage;
}
