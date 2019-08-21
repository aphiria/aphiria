<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Aphiria\Routing\Builders\RouteBuilderRegistry;

/**
 * Defines the interface for route annotation registerers
 */
interface IRouteAnnotationRegistrant
{
    /**
     * Registers routes
     *
     * @param RouteBuilderRegistry $routes The registry to register routes to
     */
    public function registerRoutes(RouteBuilderRegistry $routes): void;
}
