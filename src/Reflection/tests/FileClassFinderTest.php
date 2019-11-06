<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection\Tests;

use Aphiria\Reflection\FileClassFinder;
use Aphiria\Reflection\Tests\Mocks\ClassA;
use Aphiria\Reflection\Tests\Mocks\ClassB;
use Aphiria\Reflection\Tests\Mocks\Subdirectory\ClassC;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file class finder
 */
class FileClassFinderTest extends TestCase
{
    private const DIRECTORY = __DIR__ . '/Mocks';
    private FileClassFinder $finder;

    protected function setUp(): void
    {
        $this->finder = new FileClassFinder();
    }

    public function testControllersAreFoundInChildlessDirectory(): void
    {
        $expectedClasses = [ClassC::class];
        $this->assertEquals(
            $expectedClasses,
            $this->finder->findAllClasses(self::DIRECTORY . '/Subdirectory')
        );
    }

    public function testControllersAreFoundInSubdirectories(): void
    {
        $expectedClasses = [
            ClassA::class,
            ClassB::class,
            ClassC::class,
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedClasses,
            $this->finder->findAllClasses(self::DIRECTORY)
        );
    }

    public function testNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->findAllClasses(__FILE__);
    }
}
