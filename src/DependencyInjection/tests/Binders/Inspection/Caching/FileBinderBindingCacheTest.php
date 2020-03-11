<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Inspection\Caching;

use Aphiria\DependencyInjection\Binders\Inspection\Caching\FileBinderBindingCache;
use Aphiria\DependencyInjection\Binders\Inspection\UniversalBinderBinding;
use Aphiria\DependencyInjection\Tests\Binders\Inspection\Caching\Mocks\MockBinder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file binder binding cache
 */
class FileBinderBindingCacheTest extends TestCase
{
    /** string The path to the cache */
    private const FILE_PATH = __DIR__ . '/tmp/cache.txt';
    private FileBinderBindingCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileBinderBindingCache(self::FILE_PATH);
    }

    protected function tearDown(): void
    {
        if (\file_exists(self::FILE_PATH)) {
            @\unlink(self::FILE_PATH);
        }
    }

    public function testFlushRemovesTheFile(): void
    {
        \file_put_contents(self::FILE_PATH, 'foo');
        $this->cache->flush();
        $this->assertFileNotExists(self::FILE_PATH);
    }

    public function testGettingFromCacheWhenFileDoesExistReturnsBindings(): void
    {
        $expectedBindings = [new UniversalBinderBinding('foo', new MockBinder())];
        $this->cache->set($expectedBindings);
        $actualBindings = $this->cache->get();
        $this->assertIsArray($actualBindings);
        $this->assertCount(1, $actualBindings);
        // Only check for equality because they won't have the same identity
        $this->assertEquals($expectedBindings[0], $actualBindings[0]);
    }

    public function testGettingFromCacheWhenFileDoesNotExistReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }
}
