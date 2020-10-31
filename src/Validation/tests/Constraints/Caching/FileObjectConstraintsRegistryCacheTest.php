<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Caching;

use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

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
        $this->assertFileDoesNotExist(self::PATH);
    }

    public function testGetOnHitReturnsConstraints(): void
    {
        $objectConstraints = new ObjectConstraintsRegistry();
        $objectConstraints->registerObjectConstraints(
            new ObjectConstraints(self::class, ['prop' => $this->createMock(IConstraint::class)])
        );
        $this->cache->set($objectConstraints);
        $this->assertEquals($objectConstraints, $this->cache->get());
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
