<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Middleware;

use Opulence\Api\Tests\Middleware\Mocks\AttributeMiddleware;

/**
 * Tests the attribute middleware
 */
class AttributeMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    /** @var AttributeMiddleware The middleware to use in tests */
    private $middleware;

    public function setUp(): void
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
