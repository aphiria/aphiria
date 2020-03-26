<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata\Caching;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\FileBinderMetadataCollectionCache;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Caching\Mocks\MockBinder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file binder metadata collection cache
 */
class FileBinderMetadataCollectionCacheTest extends TestCase
{
    /** string The path to the cache */
    private const FILE_PATH = __DIR__ . '/tmp/cache.txt';
    private FileBinderMetadataCollectionCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileBinderMetadataCollectionCache(self::FILE_PATH);
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

    public function testGettingFromCacheWhenFileDoesExistReturnsBinderMetadataCollection(): void
    {
        $expectedBinderMetadatas = new BinderMetadataCollection([new BinderMetadata(new MockBinder(), [], [])]);
        $this->cache->set($expectedBinderMetadatas);
        $actualBinderMetadatas = $this->cache->get();
        // Only check for equality because they won't have the same identity
        $this->assertEquals($expectedBinderMetadatas, $actualBinderMetadatas);
    }

    public function testGettingFromCacheWhenFileDoesNotExistReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }
}
