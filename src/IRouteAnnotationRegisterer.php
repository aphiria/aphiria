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

use ReflectionException;

/**
 * Defines the interface for route annotation registerers
 */
interface IRouteAnnotationRegisterer
{
    /**
     * Registers any routes in a class
     *
     * @param string $className The name of the class to reflect on
     * @throws ReflectionException Thrown if there was an error reflecting the class
     */
    public function registerRoutes(string $className): void;
}
