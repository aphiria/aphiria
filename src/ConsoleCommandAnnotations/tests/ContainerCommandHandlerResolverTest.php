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

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\ConsoleCommandAnnotations\ContainerCommandHandlerResolver;
use Aphiria\ConsoleCommandAnnotations\DependencyResolutionException;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the container command handler resolver
 */
class ContainerCommandHandlerResolverTest extends TestCase
{
    private ContainerCommandHandlerResolver $commandHandlerResolver;
    /** @var IContainer|MockObject */
    private IContainer $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->commandHandlerResolver = new ContainerCommandHandlerResolver($this->container);
    }

    public function testContainerIsUsedToResolveDependencies(): void
    {
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willReturn($expectedCommandHandler);
        $this->assertSame($expectedCommandHandler, $this->commandHandlerResolver->resolve('foo'));
    }

    public function testResolutionExceptionsAreConverted(): void
    {
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('foo', null));

        try {
            $this->commandHandlerResolver->resolve('foo');
            $this->fail('Failed to throw exception');
        } catch (DependencyResolutionException $ex) {
            $this->assertEquals('Could not resolve command handler', $ex->getMessage());
            $this->assertEquals('foo', $ex->getCommandHandlerClassName());
        }
    }

    public function testResolvingNonCommandHandlerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('foo does not implement ' . ICommandHandler::class);
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willReturn($this);
        $this->commandHandlerResolver->resolve('foo');
    }
}
