<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\Tests\Mocks\AttributeMiddleware;
use PHPUnit\Framework\TestCase;

class AttributeMiddlewareTest extends TestCase
{
    private AttributeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new AttributeMiddleware();
    }

    public function testGettingAttributeReturnsSameValueInSetter(): void
    {
        $this->middleware->setAttributes(['foo' => 'bar']);
        $this->assertEquals('bar', $this->middleware->getAttribute('foo'));
    }

    public function testGettingAttributeThatDoesNotExistReturnsDefaultValue(): void
    {
        $this->assertNull($this->middleware->getAttribute('foo'));
        $this->assertEquals('bar', $this->middleware->getAttribute('foo', 'bar'));
    }
}
