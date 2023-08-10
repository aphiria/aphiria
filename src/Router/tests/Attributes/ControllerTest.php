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

use Aphiria\Routing\Attributes\Controller;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $controller = new Controller('path', 'example.com', true, ['foo' => 'bar']);
        $this->assertSame('path', $controller->path);
        $this->assertSame('example.com', $controller->host);
        $this->assertTrue($controller->isHttpsOnly);
        $this->assertSame(['foo' => 'bar'], $controller->parameters);
    }
}
