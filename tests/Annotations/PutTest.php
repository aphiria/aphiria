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

use Aphiria\RouteAnnotations\Annotations\Put;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PUT annotation
 */
class PutTest extends TestCase
{
    public function testPutHttpMethodIsSet(): void
    {
        $this->assertEquals(['PUT'], (new Put([]))->httpMethods);
    }
}
