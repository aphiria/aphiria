<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleCommandAnnotations\Tests;

use Aphiria\ConsoleCommandAnnotations\FileCommandFinder;
use Aphiria\ConsoleCommandAnnotations\Tests\Mocks\Finder\CommandHandlerA;
use Aphiria\ConsoleCommandAnnotations\Tests\Mocks\Finder\CommandHandlerB;
use Aphiria\ConsoleCommandAnnotations\Tests\Mocks\Finder\Subdirectory\CommandHandlerC;
use Doctrine\Annotations\Reader;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests the file command finder
 */
class FileCommandFinderTest extends TestCase
{
    private FileCommandFinder $finder;
    /** @var Reader|MockObject */
    private Reader $reader;
    private const DIRECTORY = __DIR__ . '/Mocks/Finder';

    protected function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->finder = new FileCommandFinder($this->reader);
    }

    public function testCommandsAreFoundInChildlessDirectory(): void
    {
        $this->reader->expects($this->at(0))
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === CommandHandlerC::class;
                }),
                'Command'
            )
            ->willReturn(true);
        $expectedCommands = [CommandHandlerC::class];
        $this->assertEquals(
            $expectedCommands,
            $this->finder->findAll(self::DIRECTORY . '/Subdirectory')
        );
    }

    public function testCommandsAreFoundInSubdirectories(): void
    {
        $this->reader->expects($this->at(0))
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === CommandHandlerA::class;
                }),
                'Command'
            )
            ->willReturn(true);
        $this->reader->expects($this->at(1))
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === CommandHandlerB::class;
                }),
                'Command'
            )
            ->willReturn(true);
        $this->reader->expects($this->at(2))
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === CommandHandlerC::class;
                }),
                'Command'
            )
            ->willReturn(true);
        $expectedCommands = [
            CommandHandlerA::class,
            CommandHandlerB::class,
            CommandHandlerC::class
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedCommands,
            $this->finder->findAll(self::DIRECTORY)
        );
    }

    public function testNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->findAll(__FILE__);
    }
}
