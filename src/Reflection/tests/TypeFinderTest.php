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

use Aphiria\Reflection\TypeFinder;
use Aphiria\Reflection\Tests\Mocks\AbstractClass;
use Aphiria\Reflection\Tests\Mocks\ClassA;
use Aphiria\Reflection\Tests\Mocks\ClassB;
use Aphiria\Reflection\Tests\Mocks\IInterface;
use Aphiria\Reflection\Tests\Mocks\Subdirectory\ClassC;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the type finder
 */
class TypeFinderTest extends TestCase
{
    private const DIRECTORY = __DIR__ . '/Mocks';
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

    public function testFindAllTypesWithNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->findAllTypes(__FILE__);
    }
}
