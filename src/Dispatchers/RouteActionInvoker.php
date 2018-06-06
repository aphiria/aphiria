<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Dispatchers;

use Opulence\Api\ControllerContext;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use ReflectionMethod;

/**
 * Defines the route invoker
 */
class RouteActionInvoker implements IRouteActionInvoker
{
    /**
     * @inheritdoc
     */
    public function invokeRouteAction(ControllerContext $controllerContext): IHttpResponseMessage
    {
        $matchedRoute = $controllerContext->getMatchedRoute();
        $routeAction = $matchedRoute->getAction();
        $reflectionMethod = new ReflectionMethod($routeAction->getClassName(), $routeAction->getMethodName());

        if ($reflectionMethod->isPrivate()) {
            // Todo: Throw some type of exception
        }

        // Todo: Actually start inspecting parameters
        // Todo: Remove this dummy return value
        return new Response();
    }
}
