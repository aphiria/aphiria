<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Closure;

/**
 * Defines a route action that uses a closure
 */
final class ClosureRouteAction extends RouteAction
{
    /**
     * @param Closure $closure The closure the route routes to
     */
    public function __construct(Closure $closure)
    {
        parent::__construct(null, null, $closure);
    }
}
