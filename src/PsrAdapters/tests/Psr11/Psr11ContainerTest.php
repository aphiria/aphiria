<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    /** @var IContainer|MockObject */
    private IContainer $aphiriaContainer;
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
        $this->expectExceptionMessage('Failed to resolve foo');
        $this->aphiriaContainer->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('foo', new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with('foo')
            ->willReturn(false);
        $this->psr11Container->get('foo');
    }

    public function testGetThrowsExceptionWhenNoBindingExists(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No binding found for foo');
        $this->aphiriaContainer->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('foo', new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with('foo')
            ->willReturn(true);
        $this->psr11Container->get('foo');
    }

    public function testHasReturnsFalseWhenContainerCannotResolveSomethingWithABinding(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('foo', new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with('foo')
            ->willReturn(false);
        $this->assertFalse($this->psr11Container->has('foo'));
    }

    public function testHasReturnsFalseWhenContainerCannotResolveSomethingWithoutABinding(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('foo', new UniversalContext()));
        $this->aphiriaContainer->method('hasBinding')
            ->with('foo')
            ->willReturn(true);
        $this->assertFalse($this->psr11Container->has('foo'));
    }

    public function testHasReturnsTrueWhenTheContainerCanResolveSomething(): void
    {
        $this->aphiriaContainer->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->assertTrue($this->psr11Container->has(self::class));
    }
}
