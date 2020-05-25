<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Reflection\Binders;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Reflection\Binders\TypeReflectorBinder;
use Aphiria\Reflection\AggregateTypeReflector;
use Aphiria\Reflection\ITypeReflector;
use Aphiria\Reflection\PhpDocTypeReflector;
use Aphiria\Reflection\ReflectionTypeReflector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeReflectorBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private TypeReflectorBinder $binder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new TypeReflectorBinder();
    }

    public function testAggregateTypeReflectorIsBound(): void
    {
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with([ITypeReflector::class, AggregateTypeReflector::class], $this->isInstanceOf(AggregateTypeReflector::class));
        $this->binder->bind($this->container);
    }

    public function testPhpDocTypeReflectorIsBound(): void
    {
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(PhpDocTypeReflector::class, $this->isInstanceOf(PhpDocTypeReflector::class));
        $this->binder->bind($this->container);
    }

    public function testReflectionTypeReflectorIsBound(): void
    {
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(ReflectionTypeReflector::class, $this->isInstanceOf(ReflectionTypeReflector::class));
        $this->binder->bind($this->container);
    }
}
