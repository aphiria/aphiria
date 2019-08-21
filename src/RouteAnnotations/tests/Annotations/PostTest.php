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

use Aphiria\RouteAnnotations\Annotations\Post;
use PHPUnit\Framework\TestCase;

/**
 * Tests the POST annotation
 */
class PostTest extends TestCase
{
    public function testPostHttpMethodIsSet(): void
    {
        $this->assertEquals(['POST'], (new Post([]))->httpMethods);
    }
}
