<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests;

use Aphiria\RouteAnnotations\FileControllerFinder;
use Aphiria\RouteAnnotations\Tests\Mocks\Finder\ControllerA;
use Aphiria\RouteAnnotations\Tests\Mocks\Finder\ControllerB;
use Aphiria\RouteAnnotations\Tests\Mocks\Finder\ControllerC;
use Aphiria\RouteAnnotations\Tests\Mocks\Finder\Subdirectory\ControllerD;
use Doctrine\Annotations\Reader;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests the controller finder
 */
class FileControllerFinderTest extends TestCase
{
    private FileControllerFinder $controllerFinder;
    /** @var Reader|MockObject */
    private Reader $reader;
    private const CONTROLLER_DIRECTORY = __DIR__ . '/Mocks/Finder';
    private string $topLevelControllerNamespace = '';

    protected function setup(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->controllerFinder = new FileControllerFinder($this->reader);
        $topLevelControllerNamePieces = explode('\\', ControllerA::class);
        $this->topLevelControllerNamespace = implode(
            '\\',
            array_slice($topLevelControllerNamePieces, 0, -1)
        );
    }

    public function testControllersAreFoundInChildlessDirectory(): void
    {
        $expectedControllers = [ControllerD::class];
        $this->assertEquals(
            $expectedControllers,
            $this->controllerFinder->findAll(self::CONTROLLER_DIRECTORY . '/Subdirectory')
        );
    }

    public function testControllersAreFoundInSubdirectories(): void
    {
        $this->reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === ControllerC::class;
                }),
                'Controller'
            )
            ->willReturn(true);
        $expectedControllers = [
            ControllerA::class,
            ControllerB::class,
            ControllerC::class,
            ControllerD::class,
        ];
        // We don't care so much about the ordering
        $this->assertEqualsCanonicalizing(
            $expectedControllers,
            $this->controllerFinder->findAll(self::CONTROLLER_DIRECTORY)
        );
    }

    public function testControllersWithControllerAnnotationAreFound(): void
    {
        $this->reader->expects($this->once())
            ->method('getClassAnnotation')
            ->with(
                $this->callback(function (ReflectionClass $class) {
                    return $class->name === ControllerC::class;
                }),
                'Controller'
            )
            ->willReturn(true);
        $this->assertContains(ControllerC::class, $this->controllerFinder->findAll(self::CONTROLLER_DIRECTORY));
    }

    public function testNonDirectoryPathThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->controllerFinder->findAll(__FILE__);
    }
}
