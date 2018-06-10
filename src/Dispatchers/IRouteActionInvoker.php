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
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\IHttpResponseMessage;

/**
 * Defines the interface for route action invokers to implement
 */
interface IRouteActionInvoker
{
    /**
     * Invokes a route action
     *
     * @param ControllerContext $controllerContext The current controller context
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was any error processing the request
     */
    public function invokeRouteAction(ControllerContext $controllerContext): IHttpResponseMessage;
}
