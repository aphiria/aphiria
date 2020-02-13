<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;

/**
 * Defines the interface for route action invokers to implement
 */
interface IRouteActionInvoker
{
    /**
     * Invokes a route action
     *
     * @param callable $routeActionDelegate The route action delegate to invoke
     * @param IHttpRequestMessage $request The current request
     * @param array $routeVariables The route variables
     * @return IHttpResponseMessage The response
     * @throws Exception Thrown if there was any error processing the request
     */
    public function invokeRouteAction(
        callable $routeActionDelegate,
        IHttpRequestMessage $request,
        array $routeVariables
    ): IHttpResponseMessage;
}
