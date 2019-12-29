<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Caching;

use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\Tests\Constraints\Mocks\MockConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file constraint registry cache
 */
class FileObjectConstraintsRegistryCacheTest extends TestCase
{
    /** @var string The path to the constraint cache */
    private const PATH = __DIR__ . '/tmp/constraint.cache';
    private FileObjectConstraintsRegistryCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileObjectConstraintsRegistryCache(self::PATH);
    }

    protected function tearDown(): void
    {
        if (\file_exists(self::PATH)) {
            @\unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        \file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFileNotExists(self::PATH);
    }

    public function testGetOnHitReturnsConstraints(): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        // We are explicitly using an actual class here because Opis has trouble serializing mocks/anonymous classes
        $objectConstraints->registerObjectConstraints(new ObjectConstraints('foo', ['prop' => new MockConstraint()]));
        // We have to clone the objectConstraints because serializing them will technically alter closure/serialized closure property values
        $expectedConstraints = clone $objectConstraints;
        $this->cache->set($objectConstraints);
        $this->assertEquals($expectedConstraints, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testHasReturnsExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        \file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new ObjectConstraintsRegistry());
        $this->assertFileExists(self::PATH);
    }
}
