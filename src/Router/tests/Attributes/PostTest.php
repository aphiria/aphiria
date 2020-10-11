<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPropertiesAreAllSetInConstructor(): void
    {
        $route = new Post('path', 'example.com', 'name', true, ['foo' => 'bar']);
        $this->assertSame(['POST'], $route->httpMethods);
        $this->assertSame('path', $route->path);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('name', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $route->attributes);
    }
}
