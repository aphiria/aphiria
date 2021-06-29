<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests\Handlers;

use Aphiria\Sessions\Handlers\ArraySessionDriver;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class ArraySessionDriverTest extends TestCase
{
    private ArraySessionDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new ArraySessionDriver();
    }

    public function testDeleteRemovesSessionFromArray(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Session with ID foo does not exist');
        $this->driver->set('foo', 'bar');
        $this->driver->delete('foo');
        $this->driver->get('foo');
    }

    public function testGcDoesNothing(): void
    {
        $this->driver->set('foo', 'bar');
        $this->assertEquals(0, $this->driver->gc(0));
        $this->assertSame('bar', $this->driver->get('foo'));
    }

    public function testGettingNonExistentSessionThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Session with ID non-existent does not exist');
        $this->assertEmpty($this->driver->get('non-existent'));
    }

    public function testSettingDataMakesItGettable(): void
    {
        $this->driver->set('foo', 'bar');
        $this->assertSame('bar', $this->driver->get('foo'));
    }
}
