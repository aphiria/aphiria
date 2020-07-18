<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders;

use Aphiria\DependencyInjection\Binders\FileBinderFinder;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Finder\BinderA;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Finder\BinderB;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Finder\Subdirectory\BinderC;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileBinderFinderTest extends TestCase
{
    private const BINDER_DIRECTORY = __DIR__ . '/Mocks/Finder';
    private FileBinderFinder $binderFinder;

    protected function setUp(): void
    {
        $this->binderFinder = new FileBinderFinder();
    }

    public function testBindersAreFoundInChildlessDirectory(): void
    {
        $expectedBinders = [BinderC::class];
        $this->assertEquals(
            $expectedBinders,
            $this->binderFinder->findAll(self::BINDER_DIRECTORY . '/Subdirectory')
        );
    }

    public function testBindersAreFoundInSubdirectories(): void
    {
        $expectedBinders = [
            BinderA::class,
            BinderB::class,
            BinderC::class,
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedBinders,
            $this->binderFinder->findAll(self::BINDER_DIRECTORY)
        );
    }

    public function testNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->binderFinder->findAll(__FILE__);
    }
}
