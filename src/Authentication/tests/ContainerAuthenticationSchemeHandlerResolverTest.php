<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
    public function testResolveRethrowsContainerResolutionException(): void
    {
        /** @var IAuthenticationSchemeHandler<AuthenticationSchemeOptions> $schemeHandler */
        $schemeHandler = $this->createMock(IAuthenticationSchemeHandler::class);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve ' . $schemeHandler::class);
        $container = $this->createMock(IContainer::class);
        $container->expects($this->once())
            ->method('resolve')
            ->with($schemeHandler::class)
            ->willThrowException(new ResolutionException($schemeHandler::class, new UniversalContext()));
        $resolver = new ContainerAuthenticationSchemeHandlerResolver($container);
        $resolver->resolve($schemeHandler::class);
    }
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
}
