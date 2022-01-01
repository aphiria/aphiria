<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Tests\Psr11;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\UniversalContext;
use Aphiria\PsrAdapters\Psr11\ContainerException;
use Aphiria\PsrAdapters\Psr11\NotFoundException;
use Aphiria\PsrAdapters\Psr11\Psr11Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class Psr11ContainerTest extends TestCase
{
    private IContainer&MockObject $aphiriaContainer;
    private Psr11Container $psr11Container;

    protected function setUp(): void
    {
        $this->aphiriaContainer = $this->createMock(IContainer::class);
        $this->psr11Container = new Psr11Container($this->aphiriaContainer);
    }

    public function testGetReturnsResolvedInterface(): void
    {
        $this->aphiriaContainer->expects($this->once())
            ->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->assertSame($this, $this->psr11Container->get(self::class));
    }

    public function testGetThrowsExceptionWhenItCannotAutoWire(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Failed to resolve ' . self::class);
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willThrowException(new ResolutionException(self::class, new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with(self::class)
            ->willReturn(false);
        $this->psr11Container->get(self::class);
    }

    public function testGetThrowsExceptionWhenNoBindingExists(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No binding found for ' . self::class);
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willThrowException(new ResolutionException(self::class, new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with(self::class)
            ->willReturn(true);
        $this->psr11Container->get(self::class);
    }

    public function testHasReturnsFalseWhenContainerCannotResolveSomethingWithABinding(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willThrowException(new ResolutionException(self::class, new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with(self::class)
            ->willReturn(false);
        $this->assertFalse($this->psr11Container->has(self::class));
    }

    public function testHasReturnsFalseWhenContainerCannotResolveSomethingWithoutABinding(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willThrowException(new ResolutionException(self::class, new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with(self::class)
            ->willReturn(true);
        $this->assertFalse($this->psr11Container->has(self::class));
    }

    public function testHasReturnsTrueWhenTheContainerCanResolveSomething(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->assertTrue($this->psr11Container->has(self::class));
    }
}
