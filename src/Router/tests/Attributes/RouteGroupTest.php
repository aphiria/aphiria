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

use Aphiria\Routing\Attributes\RouteGroup;
use PHPUnit\Framework\TestCase;

class RouteGroupTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $routeGroup = new RouteGroup('path', 'example.com', true, ['foo' => 'bar']);
        $this->assertSame('path', $routeGroup->path);
        $this->assertSame('example.com', $routeGroup->host);
        $this->assertTrue($routeGroup->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $routeGroup->parameters);
    }
}
