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

use Aphiria\RouteAnnotations\Annotations\Patch;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PATCH annotation
 */
class PatchTest extends TestCase
{
    public function testPatchHttpMethodIsSet(): void
    {
        $this->assertEquals(['PATCH'], (new Patch([]))->httpMethods);
    }
}
