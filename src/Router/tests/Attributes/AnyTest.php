<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\Any;
use PHPUnit\Framework\TestCase;

class AnyTest extends TestCase
{
    public function testPropertiesAreAllSetInConstructor(): void
    {
        $route = new Any('path', 'example.com', 'name', true, ['foo' => 'bar']);
        $this->assertEmpty($route->httpMethods);
        $this->assertSame('path', $route->path);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('name', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $route->parameters);
    }
}
