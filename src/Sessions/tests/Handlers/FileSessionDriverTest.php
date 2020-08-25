<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Tests\Handlers;

use Aphiria\Sessions\Handlers\FileSessionDriver;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class FileSessionDriverTest extends TestCase
{
    private const BASE_PATH = __DIR__ . '/tmp';
    private FileSessionDriver $driver;

    public static function setUpBeforeClass(): void
    {
        if (!is_dir(self::BASE_PATH)) {
            mkdir(self::BASE_PATH);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $files = glob(self::BASE_PATH . '/*');

        foreach ($files as $file) {
            is_dir($file) ? rmdir($file) : unlink($file);
        }

        rmdir(self::BASE_PATH);
    }

    protected function setUp(): void
    {
        $this->driver = new FileSessionDriver(self::BASE_PATH);
    }

    public function testDeleteDeletesFiles(): void
    {
        $this->driver->set('foo', 'bar');
        $this->driver->delete('foo');
        $this->assertFileDoesNotExist(self::BASE_PATH . '/foo');
    }

    public function testGarbageCollectionDeletesFiles(): void
    {
        $this->driver->set('foo', 'bar');
        $this->driver->set('bar', 'baz');
        $this->driver->gc(-1);
        $this->assertEquals([], glob(self::BASE_PATH . '/*'));
    }

    public function testGettingNonExistentSessionThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Session with ID non-existent does not exist');
        $this->assertEmpty($this->driver->get('non-existent'));
    }

    public function testGettingReturnsDataWrittenToFile(): void
    {
        \file_put_contents(self::BASE_PATH . '/foo', 'bar');
        $this->assertSame('bar', $this->driver->get('foo'));
    }

    public function testSettingDataWritesItToFile(): void
    {
        $this->driver->set('foo', 'bar');
        $this->assertSame('bar', \file_get_contents(self::BASE_PATH . '/foo'));
    }
}
