<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Regexes\Caching;

use Opulence\Routing\Matchers\Regexes\GroupRegex;
use Opulence\Routing\Matchers\Regexes\GroupRegexCollection;

/**
 * Tests the file group regex cache
 */
class FileGroupRegexCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    /** @var FileGroupRegexCache The cache to test */
    private $cache = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->cache = new FileGroupRegexCache(self::PATH);
    }

    /**
     * Tears down the tests
     */
    public function tearDown()
    {
        if (file_exists(self::PATH)) {
            @unlink(self::PATH);
        }
    }

    /**
     * Tests that flushing deletes the file
     */
    public function testFlushDeletesFile() : void
    {
        file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFalse(file_exists(self::PATH));
    }

    /**
     * Tests a hit returns the regexes
     */
    public function testGetOnHitReturnsRegexes() : void
    {
        $regexes = new GroupRegexCollection();
        $regexes->add('GET', new GroupRegex('foo', ['baz']));
        $this->cache->set($regexes);
        $this->assertEquals($regexes, $this->cache->get());
    }

    /**
     * Tests a miss returns null
     */
    public function testGetOnMissReturnsNull() : void
    {
        $this->assertNull($this->cache->get());
    }

    /**
     * Test that has returns the existence of the file
     */
    public function testHasReturnsExistenceOfFile() : void
    {
        $this->assertFalse($this->cache->has());
        file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    /**
     * Tests setting the cache creates the file
     */
    public function testSetCreatesTheFile() : void
    {
        $this->cache->set(new GroupRegexCollection());
        $this->assertTrue(file_exists(self::PATH));
    }
}
