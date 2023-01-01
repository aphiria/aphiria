<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\Put;
use PHPUnit\Framework\TestCase;

class PutTest extends TestCase
{
    public function testPropertiesAreAllSetInConstructor(): void
    {
        $route = new Put('path', 'example.com', 'name', true, ['foo' => 'bar']);
        $this->assertSame(['PUT'], $route->httpMethods);
        $this->assertSame('path', $route->path);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('name', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $route->parameters);
    }
}
