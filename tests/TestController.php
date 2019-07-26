<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests;

use Aphiria\RouteAnnotations\Annotations\Get;
use Aphiria\RouteAnnotations\Annotations\Middleware;

/**
 * @Middleware(className="Foo", attributes={"foo":"bar"})
 * @Middleware("someMiddleware", attributes={"foo":"bar"})
 */
final class TestController
{
    /**
     * @Get("foo")
     */
    public function route1(): void
    {

    }
}
