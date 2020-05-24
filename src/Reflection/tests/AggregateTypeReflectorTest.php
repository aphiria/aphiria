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

use Aphiria\Reflection\AggregateTypeReflector;
use Aphiria\Reflection\ITypeReflector;
use Aphiria\Reflection\Type;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AggregateTypeReflectorTest extends TestCase
{
    public function testEmptyReflectorsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('List of type reflectors cannot be empty');
        new AggregateTypeReflector([]);
    }

    public function testGetParameterTypesReturnsFirstNonNullTypes(): void
    {
        $object = new class() {
            public function foo(string $bar)
            {
            }
        };
        $expectedTypes = [new Type('string')];
        $subReflector1 = $this->createMock(ITypeReflector::class);
        $subReflector2 = $this->createMock(ITypeReflector::class);
        $subReflector1->expects($this->once())
            ->method('getParameterTypes')
            ->with(\get_class($object), 'foo', 'bar')
            ->willReturn($expectedTypes);
        $subReflector2->expects($this->never())
            ->method('getParameterTypes');
        $reflector = new AggregateTypeReflector([$subReflector1, $subReflector2]);
        $this->assertEquals($expectedTypes, $reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesReturnsNullIfNoReflectorsReturnedTypes(): void
    {
        $object = new class() {
            public function foo($bar)
            {
            }
        };
        $subReflector = $this->createMock(ITypeReflector::class);
        $subReflector->expects($this->once())
            ->method('getParameterTypes')
            ->with(\get_class($object), 'foo', 'bar')
            ->willReturn(null);
        $reflector = new AggregateTypeReflector([$subReflector]);
        $this->assertNull($reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetPropertyTypesReturnsFirstNonNullTypes(): void
    {
        $object = new class() {
            public string $foo;
        };
        $expectedTypes = [new Type('string')];
        $subReflector1 = $this->createMock(ITypeReflector::class);
        $subReflector2 = $this->createMock(ITypeReflector::class);
        $subReflector1->expects($this->once())
            ->method('getPropertyTypes')
            ->with(\get_class($object), 'foo')
            ->willReturn($expectedTypes);
        $subReflector2->expects($this->never())
            ->method('getPropertyTypes');
        $reflector = new AggregateTypeReflector([$subReflector1, $subReflector2]);
        $this->assertEquals($expectedTypes, $reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesReturnsNullIfNoReflectorsReturnedTypes(): void
    {
        $object = new class() {
            public $foo;
        };
        $subReflector = $this->createMock(ITypeReflector::class);
        $subReflector->expects($this->once())
            ->method('getPropertyTypes')
            ->with(\get_class($object), 'foo')
            ->willReturn(null);
        $reflector = new AggregateTypeReflector([$subReflector]);
        $this->assertNull($reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesReturnsFirstNonNullTypes(): void
    {
        $object = new class() {
            public function foo(): string
            {
                return '';
            }
        };
        $expectedTypes = [new Type('string')];
        $subReflector1 = $this->createMock(ITypeReflector::class);
        $subReflector2 = $this->createMock(ITypeReflector::class);
        $subReflector1->expects($this->once())
            ->method('getReturnTypes')
            ->with(\get_class($object), 'foo')
            ->willReturn($expectedTypes);
        $subReflector2->expects($this->never())
            ->method('getReturnTypes');
        $reflector = new AggregateTypeReflector([$subReflector1, $subReflector2]);
        $this->assertEquals($expectedTypes, $reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesReturnsNullIfNoReflectorsReturnedTypes(): void
    {
        $object = new class() {
            public function foo()
            {
            }
        };
        $subReflector = $this->createMock(ITypeReflector::class);
        $subReflector->expects($this->once())
            ->method('getReturnTypes')
            ->with(\get_class($object), 'foo')
            ->willReturn(null);
        $reflector = new AggregateTypeReflector([$subReflector]);
        $this->assertNull($reflector->getReturnTypes(\get_class($object), 'foo'));
    }
}
