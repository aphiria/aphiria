<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Closure;
use Exception;

/**
 * Defines the interface for route action invokers to implement
 */
interface IRouteActionInvoker
{
    /**
     * Invokes a route action
     *
     * @param Closure $routeActionDelegate The route action delegate to invoke
     * @param IRequest $request The current request
     * @param array<string, mixed> $routeVariables The route variables
     * @return IResponse The response
     * @throws Exception Thrown if there was any error processing the request
     */
    public function invokeRouteAction(
        Closure $routeActionDelegate,
        IRequest $request,
        array $routeVariables
    ): IResponse;
}
