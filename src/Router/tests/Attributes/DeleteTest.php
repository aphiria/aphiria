<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\Delete;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    public function testPropertiesAreAllSetInConstructor(): void
    {
        $route = new Delete('path', 'example.com', 'name', true, ['foo' => 'bar']);
        $this->assertSame(['DELETE'], $route->httpMethods);
        $this->assertSame('path', $route->path);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('name', $route->name);
        $this->assertTrue($route->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $route->parameters);
    }
}
