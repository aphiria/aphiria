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

use Aphiria\Reflection\ReflectionTypeReflector;
use Aphiria\Reflection\Tests\Mocks\ClassA;
use Aphiria\Reflection\Type;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ReflectionTypeReflectorTest extends TestCase
{
    private ReflectionTypeReflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new ReflectionTypeReflector();
    }

    public function testGetParameterTypesForArrayParameterCreatesArrayType(): void
    {
        $object = new class() {
            public function foo(array $bar): void
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForClassWithoutThatMethodThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar');
    }

    public function testGetParameterTypesForClassWithoutThatParameterThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
            public function foo(): void
            {
            }
        };
        $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar');
    }

    public function testGetParameterTypesForMethodWithMultipleParametersCreatesCorrectTypesForEach(): void
    {
        $object = new class() {
            public function foo(string $bar, int $baz): void
            {
            }
        };
        $this->assertEquals([new Type('string')], $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals([new Type('int')], $this->reflector->getParameterTypes(\get_class($object), 'foo', 'baz'));
    }

    public function testGetParameterTypesForNullableArrayParameterCreatesNullableArrayType(): void
    {
        $object = new class() {
            // Test a nullable type
            public function foo(?array $bar): void
            {
            }

            // Test a default null value
            public function baz(array $quz = null): void
            {
            }
        };
        $expectedTypes = [new Type('array', null, true, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'baz', 'quz'));
    }

    public function testGetParameterTypesForNullableObjectParameterCreatesNullableObjectType(): void
    {
        $object = new class() {
            // Test a nullable type
            public function foo(?ClassA $bar): void
            {
            }

            // Test a default null value
            public function baz(ClassA $quz = null): void
            {
            }
        };
        $expectedTypes = [new Type('object', ClassA::class, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'baz', 'quz'));
    }

    public function testGetParameterTypesForNullableScalarParameterCreatesScalarArrayType(): void
    {
        $object = new class() {
            // Test a nullable type
            public function foo(?string $bar): void
            {
            }

            // Test a default null value
            public function baz(string $quz = null): void
            {
            }
        };
        $expectedTypes = [new Type('string', null, true, false)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'baz', 'quz'));
    }

    public function testGetParameterTypesForObjectParameterCreatesObjectType(): void
    {
        $object = new class() {
            public function foo(ClassA $bar): void
            {
            }
        };
        $expectedTypes = [new Type('object', ClassA::class)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForParameterWithoutTypeReturnsNull(): void
    {
        $object = new class() {
            public function foo($bar): void
            {
            }
        };
        $this->assertNull($this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForScalarParameterCreatesScalarType(): void
    {
        $object = new class() {
            public function foo(string $bar): void
            {
            }
        };
        $this->assertEquals([new Type('string')], $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForSelfReturnsObjectTypesForSelf(): void
    {
        $object = new class() {
            public function foo(self $bar)
            {
            }
        };
        $expectedTypes = [new Type('object', \get_class($object))];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            public function foo(string $bar)
            {
            }
        };
        $expectedTypes = [new Type('string')];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        // Technically, we're just manually making sure that the code paths are hit via code coverage
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetPropertyTypesForArrayPropertyCreatesArrayType(): void
    {
        $object = new class() {
            public array $foo;
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForClassWithoutThatPropertyThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getPropertyTypes(\get_class($object), 'foo');
    }

    public function testGetPropertyTypesForNullableArrayPropertyCreatesNullableArrayType(): void
    {
        $object = new class() {
            public ?array $foo;
        };
        $expectedTypes = [new Type('array', null, true, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForNullableObjectPropertyCreatesNullableObjectType(): void
    {
        $object = new class() {
            public ?ClassA $foo;
        };
        $expectedTypes = [new Type('object', ClassA::class, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForNullableScalarPropertyCreatesScalarArrayType(): void
    {
        $object = new class() {
            public ?string $foo;
        };
        $expectedTypes = [new Type('string', null, true, false)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForObjectPropertyCreatesObjectType(): void
    {
        $object = new class() {
            public ClassA $foo;
        };
        $expectedTypes = [new Type('object', ClassA::class)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForPropertyWithoutTypeReturnsNull(): void
    {
        $object = new class() {
            public $foo;
        };
        $this->assertNull($this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForScalarPropertyCreatesScalarType(): void
    {
        $object = new class() {
            public string $foo;
        };
        $this->assertEquals([new Type('string')], $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            public string $foo;
        };
        $expectedTypes = [new Type('string')];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
        // Technically, we're just manually making sure that the code paths are hit via code coverage
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForArrayMethodCreatesArrayType(): void
    {
        $object = new class() {
            public function foo(): array
            {
                return [];
            }
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForClassWithoutThatMethodThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getReturnTypes(\get_class($object), 'foo');
    }

    public function testGetReturnTypesForNullableArrayMethodCreatesNullableArrayType(): void
    {
        $object = new class() {
            public function foo(): ?array
            {
                return null;
            }
        };
        $expectedTypes = [new Type('array', null, true, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForNullableObjectMethodCreatesNullableObjectType(): void
    {
        $object = new class() {
            public function foo(): ?ClassA
            {
                return null;
            }
        };
        $expectedTypes = [new Type('object', ClassA::class, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForNullableScalarMethodCreatesScalarArrayType(): void
    {
        $object = new class() {
            public function foo(): ?string
            {
                return null;
            }
        };
        $expectedTypes = [new Type('string', null, true, false)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForObjectMethodCreatesObjectType(): void
    {
        $object = new class() {
            public function foo(): ClassA
            {
                return new ClassA();
            }
        };
        $expectedTypes = [new Type('object', ClassA::class)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForMethodWithoutTypeReturnsNull(): void
    {
        $object = new class() {
            public function foo()
            {
                return null;
            }
        };
        $this->assertNull($this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForScalarMethodCreatesScalarType(): void
    {
        $object = new class() {
            public function foo(): string
            {
                return 'foo';
            }
        };
        $this->assertEquals([new Type('string')], $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForSelfReturnsObjectTypesForSelf(): void
    {
        $object = new class() {
            public function foo(): self
            {
                return $this;
            }
        };
        $expectedTypes = [new Type('object', \get_class($object))];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForVoidMethodCreatesNullType(): void
    {
        $object = new class() {
            public function foo(): void
            {
            }
        };
        $this->assertEquals([new Type('null')], $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            public function foo(): string
            {
                return '';
            }
        };
        $expectedTypes = [new Type('string')];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
        // Technically, we're just manually making sure that the code paths are hit via code coverage
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }
}
