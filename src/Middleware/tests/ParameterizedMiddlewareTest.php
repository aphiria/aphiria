<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\Tests\Mocks\ParameterizedMiddleware;
use PHPUnit\Framework\TestCase;

class ParameterizedMiddlewareTest extends TestCase
{
    private ParameterizedMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new ParameterizedMiddleware();
    }

    public function testGettingParameterReturnsSameValueInSetter(): void
    {
        $this->middleware->setParameters(['foo' => 'bar']);
        $this->assertSame('bar', $this->middleware->getParameter('foo'));
    }

    public function testGettingParameterThatDoesNotExistReturnsDefaultValue(): void
    {
        $this->assertNull($this->middleware->getParameter('foo'));
        $this->assertSame('bar', $this->middleware->getParameter('foo', 'bar'));
    }
}
