<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

/**
 * Defines a route action
 */
class RouteAction
{
    /** @var string The name of the class the route routes to */
    public string $className;
    /** @var string The name of the method the route routes to */
    public string $methodName;

    /**
     * @param string $className The name of the class the route routes to
     * @param string $methodName The name of the method the route routes to
     */
    public function __construct(string $className, string $methodName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
    }
}
