<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection\Tests;

use Aphiria\Reflection\Tests\Mocks\Finder\AbstractClass;
use Aphiria\Reflection\Tests\Mocks\Finder\ClassA;
use Aphiria\Reflection\Tests\Mocks\Finder\ClassB;
use Aphiria\Reflection\Tests\Mocks\Finder\IInterface;
use Aphiria\Reflection\Tests\Mocks\Finder\Subdirectory\ClassC;
use Aphiria\Reflection\TypeFinder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TypeFinderTest extends TestCase
{
    private const DIRECTORY = __DIR__ . '/Mocks/Finder';
    private TypeFinder $finder;

    protected function setUp(): void
    {
        $this->finder = new TypeFinder();
    }

    public function testFindAllClassesOnlyReturnsClasses(): void
    {
        $expectedClasses = [
            ClassA::class,
            ClassB::class,
            ClassC::class
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedClasses,
            $this->finder->findAllClasses(self::DIRECTORY, true)
        );
    }

    public function testFindAllClassesAndAbstractClassesOnlyReturnsClassesAndAbstractClasses(): void
    {
        $expectedClasses = [
            ClassA::class,
            ClassB::class,
            ClassC::class,
            AbstractClass::class
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedClasses,
            $this->finder->findAllClasses(self::DIRECTORY, true, true)
        );
    }

    public function testFindAllInterfacesOnlyReturnsInterfaces(): void
    {
        $expectedInterfaces = [IInterface::class];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedInterfaces,
            $this->finder->findAllInterfaces(self::DIRECTORY, true)
        );
    }

    public function testFindAllSubTypesOnlyReturnsSubTypes(): void
    {
        $expectedSubTypes = [ClassB::class];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedSubTypes,
            $this->finder->findAllSubtypesOfType(ClassA::class, self::DIRECTORY, true)
        );
    }

    public function testFindAllTypesAreFoundInChildlessDirectory(): void
    {
        $expectedClasses = [ClassC::class];
        $this->assertEquals(
            $expectedClasses,
            $this->finder->findAllTypes(self::DIRECTORY . '/Subdirectory')
        );
    }

    public function testFindAllTypesAreFoundInSubdirectories(): void
    {
        $expectedClasses = [
            ClassA::class,
            ClassB::class,
            ClassC::class,
            AbstractClass::class,
            IInterface::class
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedClasses,
            $this->finder->findAllTypes(self::DIRECTORY, true)
        );
    }

    public function testFindAllTypesWithInvalidDirectoryParameterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directories must be a string or array of strings');
        $this->finder->findAllTypes($this);
    }

    public function testFindAllTypesWithNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->findAllTypes(__FILE__);
    }
}
