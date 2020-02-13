<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers;

use InvalidArgumentException;
use Aphiria\DependencyInjection\Bootstrappers\FileBootstrapperFinder;
use Aphiria\DependencyInjection\Tests\Bootstrappers\Mocks\Finder\BootstrapperA;
use Aphiria\DependencyInjection\Tests\Bootstrappers\Mocks\Finder\BootstrapperB;
use Aphiria\DependencyInjection\Tests\Bootstrappers\Mocks\Finder\Subdirectory\BootstrapperC;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file bootstrapper finder
 */
class FileBootstrapperFinderTest extends TestCase
{
    private const BOOTSTRAPPER_DIRECTORY = __DIR__ . '/Mocks/Finder';
    private FileBootstrapperFinder $bootstrapperFinder;

    protected function setUp(): void
    {
        $this->bootstrapperFinder = new FileBootstrapperFinder();
    }

    public function testBootstrappersAreFoundInChildlessDirectory(): void
    {
        $expectedBootstrappers = [BootstrapperC::class];
        $this->assertEquals(
            $expectedBootstrappers,
            $this->bootstrapperFinder->findAll(self::BOOTSTRAPPER_DIRECTORY . '/Subdirectory')
        );
    }

    public function testBootstrappersAreFoundInSubdirectories(): void
    {
        $expectedBootstrappers = [
            BootstrapperA::class,
            BootstrapperB::class,
            BootstrapperC::class,
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedBootstrappers,
            $this->bootstrapperFinder->findAll(self::BOOTSTRAPPER_DIRECTORY)
        );
    }

    public function testNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->bootstrapperFinder->findAll(__FILE__);
    }
}
