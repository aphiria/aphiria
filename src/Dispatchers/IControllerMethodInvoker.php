<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Api\Controller;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Routing\Matchers\MatchedRoute;

/**
 * Defines the interface for controller method invokers to implement
 */
interface IControllerMethodInvoker
{
    /**
     * Invokes a controller method
     *
     * @param Controller $controller The instance of the controller to call
     * @param string $methodName The name of the method to invoke
     * @param IHttpRequestMessage $request The current request
     * @param MatchedRoute $matchedRoute The matched route
     * @return IHttpResponseMessage The response
     */
    public function invokeMethod(
        Controller $controller,
        string $methodName,
        IHttpRequestMessage $request,
        MatchedRoute $matchedRoute
    ): IHttpResponseMessage;
}
