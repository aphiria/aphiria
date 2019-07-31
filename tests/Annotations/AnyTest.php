<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests\Annotations;

use Aphiria\RouteAnnotations\Annotations\Any;
use PHPUnit\Framework\TestCase;

/**
 * Tests the annotation for any HTTP method
 */
class AnyTest extends TestCase
{
    public function testNoHttpMethodsSet(): void
    {
        $this->assertEmpty((new Any([]))->httpMethods);
    }
}
