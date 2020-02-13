<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers\Inspection\Caching;

use Aphiria\DependencyInjection\Bootstrappers\Inspection\Caching\FileBootstrapperBindingCache;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\UniversalBootstrapperBinding;
use Aphiria\DependencyInjection\Tests\Bootstrappers\Inspection\Caching\Mocks\MockBootstrapper;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file bootstrapper binding cache
 */
class FileBootstrapperBindingCacheTest extends TestCase
{
    /** string The path to the cache */
    private const FILE_PATH = __DIR__ . '/tmp/cache.txt';
    private FileBootstrapperBindingCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileBootstrapperBindingCache(self::FILE_PATH);
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
        $expectedBindings = [new UniversalBootstrapperBinding('foo', new MockBootstrapper())];
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
