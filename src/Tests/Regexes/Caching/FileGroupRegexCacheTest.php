<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Regexes\Caching;

use Opulence\Routing\Regexes\Caching\FileGroupRegexCache;
use Opulence\Routing\Regexes\GroupRegex;
use Opulence\Routing\Regexes\GroupRegexCollection;

/**
 * Tests the file group regex cache
 */
class FileGroupRegexCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    /** @var FileGroupRegexCache The cache to test */
    private $cache;

    public function setUp(): void
    {
        $this->cache = new FileGroupRegexCache(self::PATH);
    }

    public function tearDown()
    {
        if (file_exists(self::PATH)) {
            @unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFalse(file_exists(self::PATH));
    }

    public function testGetOnHitReturnsRegexes(): void
    {
        $regexes = new GroupRegexCollection();
        $regexes->add('GET', new GroupRegex('foo', ['baz']));
        $this->cache->set($regexes);
        $this->assertEquals($regexes, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testHasReturnsExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new GroupRegexCollection());
        $this->assertTrue(file_exists(self::PATH));
    }
}
