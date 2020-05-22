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

use Aphiria\Reflection\PhpDocTypeReflector;
use Aphiria\Reflection\Type;
use Closure;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class PhpDocTypeReflectorTest extends TestCase
{
    private PhpDocTypeReflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new PhpDocTypeReflector();
    }

    public function testGetPropertyTypesForClassWithoutThatPropertyThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getPropertyTypes(\get_class($object), 'foo');
    }

    public function testGetPropertyTypesForCollectionReturnsTypesWithKeyAndValueTypesSet(): void
    {
        $object = new class() {
            /** @var \Foo<string, string> */
            public $foo;
        };
        $expectedTypes = [new Type('\Foo', 'Foo', false, true, new Type('string'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForCompoundTypeReturnsAllTypes(): void
    {
        $object = new class() {
            /** @var string|int */
            public $foo;
        };
        $expectedTypes = [
            new Type('string'),
            new Type('int')
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForNullableCompoundTypeReturnsNullableTypes(): void
    {
        $object = new class() {
            /** @var string|int|null */
            public $foo;
        };
        $expectedTypes = [
            new Type('string', null, true),
            new Type('int', null, true)
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesForNullableTypeReturnsNullableTypes(): void
    {
        // Test both ways of declaring something nullable
        $object = new class() {
            /** @var string|null */
            public $foo;
            /** @var ?string */
            public $bar;
        };
        $expectedTypes = [new Type('string', null, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'bar'));
    }

    public function testGetPropertyTypesInfersTypedArrays(): void
    {
        $object = new class() {
            /** @var array */
            public $foo;
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesInfersTypedMixedArrays(): void
    {
        $object = new class() {
            /** @var mixed[] */
            public $foo;
        };
        // We cannot infer the key/value types from mixed arrays because each key/value might by a different type
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesInfersTypedObjectArrays(): void
    {
        $object = new class() {
            /** @var Closure[] */
            public $foo;
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('object', Closure::class))];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesInfersTypedScalarArrays(): void
    {
        $object = new class() {
            /** @var string[] */
            public $foo;
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetPropertyTypesNormalizesPhpDocTypesToPhpTypes(): void
    {
        $object = new class() {
            /** @var boolean */
            public $bool;
            /** @var double */
            public $float;
            /** @var integer */
            public $int;
        };
        $this->assertEquals([new Type('bool')], $this->reflector->getPropertyTypes(\get_class($object), 'bool'));
        $this->assertEquals([new Type('float')], $this->reflector->getPropertyTypes(\get_class($object), 'float'));
        $this->assertEquals([new Type('int')], $this->reflector->getPropertyTypes(\get_class($object), 'int'));
    }

    public function testGetPropertyTypesReturnsNullIfNoTypesCanBeFound(): void
    {
        $object = new class() {
            public $foo;
        };
        $this->assertNull($this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }
}
