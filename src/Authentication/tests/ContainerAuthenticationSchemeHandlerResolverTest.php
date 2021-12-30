<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests;

use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\ContainerAuthenticationSchemeHandlerResolver;
use Aphiria\Authentication\Schemes\IAuthenticationSchemeHandler;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ContainerAuthenticationSchemeHandlerResolverTest extends TestCase
{
    public function testResolveUsesContainerToResolveHandler(): void
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('resolve')
            ->with($schemeHandler::class)
            ->willReturn($schemeHandler);
        $resolver = new ContainerAuthenticationSchemeHandlerResolver($container);
        $this->assertSame($schemeHandler, $resolver->resolve($schemeHandler::class));
    }

    public function testResolveRethrowsContainerResolutionException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve ' . $this::class);
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('resolve')
            ->with($this::class)
            ->willThrowException(new ResolutionException($this::class, new UniversalContext()));
        $resolver = new ContainerAuthenticationSchemeHandlerResolver($container);
        $resolver->resolve($this::class);
    }
}
