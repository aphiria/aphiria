<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Bootstrappers;

use Aphiria\RouteAnnotations\IRouteAnnotationRegistrant;
use Aphiria\RouteAnnotations\ReflectionRouteAnnotationRegistrant;
use Opulence\Ioc\Bootstrappers\Bootstrapper;
use Opulence\Ioc\IContainer;

/**
 * Defines the bootstrapper for route annotations
 */
final class RouteAnnotationBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function registerBindings(IContainer $container): void
    {
        $routeAnnotationRegistrant = new ReflectionRouteAnnotationRegistrant(__DIR__ . '/src');
        $container->bindInstance(IRouteAnnotationRegistrant::class, $routeAnnotationRegistrant);
    }
}
