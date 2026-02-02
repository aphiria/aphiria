<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers\Caching;

use Aphiria\Application\Discoverers\Caching\FileDiscoveredComponentCache;
use Aphiria\Application\Discoverers\ComponentDiscovererDiscoverer;
use Aphiria\Application\Discoverers\DiscoveredComponent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class FileDiscoveredComponentCacheTest extends TestCase
{
    private FileDiscoveredComponentCache $cache;
    private string $cacheDirectory;

    protected function setUp(): void
    {
        $this->cacheDirectory = \sys_get_temp_dir() . '/aphiria-cache-test-' . \uniqid();
        $this->cache = new FileDiscoveredComponentCache($this->cacheDirectory);
    }

    protected function tearDown(): void
    {
        // Clean up cache files
        if (\is_dir($this->cacheDirectory)) {
            foreach (\glob($this->cacheDirectory . '/*') as $file) {
                @\unlink($file);
            }

            @\rmdir($this->cacheDirectory);
        }
    }

    public function testFlushDeletesAllCacheFiles(): void
    {
        $discovererClassName1 = ComponentDiscovererDiscoverer::class;
        $discovererClassName2 = 'SomeOtherDiscoverer';
        $components = [new DiscoveredComponent(new ReflectionClass(self::class))];

        $this->cache->set($discovererClassName1, $components);
        $this->cache->set($discovererClassName2, $components);
        $this->assertTrue($this->cache->has($discovererClassName1));
        $this->assertTrue($this->cache->has($discovererClassName2));

        $this->cache->flush();

        $this->assertFalse($this->cache->has($discovererClassName1));
        $this->assertFalse($this->cache->has($discovererClassName2));
    }

    public function testGetReturnsComponentsFromCache(): void
    {
        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $components = [new DiscoveredComponent(new ReflectionClass(self::class))];

        $this->cache->set($discovererClassName, $components);
        $cachedComponents = $this->cache->get($discovererClassName);

        $this->assertEquals($components, $cachedComponents);
    }

    public function testGetReturnsNullWhenCacheDoesNotExist(): void
    {
        $this->assertNull($this->cache->get('NonExistentDiscoverer'));
    }

    public function testGetThrowsExceptionWhenCachedArrayContainsInvalidType(): void
    {
        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $path = $this->cacheDirectory . '/' . \str_replace('\\', '_', $discovererClassName) . '.cache';

        if (!\is_dir($this->cacheDirectory)) {
            \mkdir($this->cacheDirectory, 0755, true);
        }

        \file_put_contents($path, \serialize(['not a DiscoveredComponent']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cached discovered components must be instances of');
        $this->cache->get($discovererClassName);
    }

    public function testGetThrowsExceptionWhenCachedValueIsNotArray(): void
    {
        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $path = $this->cacheDirectory . '/' . \str_replace('\\', '_', $discovererClassName) . '.cache';

        if (!\is_dir($this->cacheDirectory)) {
            \mkdir($this->cacheDirectory, 0755, true);
        }

        \file_put_contents($path, \serialize('not an array'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cached discovered components must be an array');
        $this->cache->get($discovererClassName);
    }

    public function testHasReturnsFalseWhenCacheDoesNotExist(): void
    {
        $this->assertFalse($this->cache->has('NonExistentDiscoverer'));
    }

    public function testHasReturnsTrueWhenCacheExists(): void
    {
        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $components = [new DiscoveredComponent(new ReflectionClass(self::class))];

        $this->cache->set($discovererClassName, $components);

        $this->assertTrue($this->cache->has($discovererClassName));
    }

    public function testSetCreatesDirectoryIfNotExists(): void
    {
        $this->assertDirectoryDoesNotExist($this->cacheDirectory);

        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $components = [new DiscoveredComponent(new ReflectionClass(self::class))];

        $this->cache->set($discovererClassName, $components);

        $this->assertDirectoryExists($this->cacheDirectory);
    }

    public function testSetStoresComponentsInCache(): void
    {
        $discovererClassName = ComponentDiscovererDiscoverer::class;
        $components = [new DiscoveredComponent(new ReflectionClass(self::class))];

        $this->cache->set($discovererClassName, $components);

        $path = $this->cacheDirectory . '/' . \str_replace('\\', '_', $discovererClassName) . '.cache';
        $this->assertFileExists($path);

        $cachedComponents = \unserialize(\file_get_contents($path));
        $this->assertEquals($components, $cachedComponents);
    }
}
