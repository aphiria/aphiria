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

    public function testGetParameterTypesForClassWithoutThatMethodThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar');
    }

    public function testGetParameterTypesForClassWithMultipleParametersUsesCorrectParam(): void
    {
        $object = new class() {
            /**
             * @param string $bar
             * @param int $baz
             */
            public function foo($bar, $baz)
            {
            }
        };
        $this->assertEquals([new Type('string')], $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals([new Type('int')], $this->reflector->getParameterTypes(\get_class($object), 'foo', 'baz'));
    }

    public function testGetParameterTypesForClassWithoutThatParameterThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
            public function foo()
            {
            }
        };
        $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar');
    }

    public function testGetParameterTypesForCollectionReturnsTypesWithKeyAndValueTypesSet(): void
    {
        $object = new class() {
            /** @param Foo<string, string> $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('object', 'Foo', false, true, new Type('string'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForCompoundTypeReturnsAllTypes(): void
    {
        $object = new class() {
            /** @param string|int $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [
            new Type('string'),
            new Type('int')
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForNullableCompoundTypeReturnsNullableTypes(): void
    {
        $object = new class() {
            /** @param string|int|null $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [
            new Type('string', null, true),
            new Type('int', null, true)
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesForNullableTypeReturnsNullableTypes(): void
    {
        // Test both ways of declaring something nullable
        $object = new class() {
            /** @param string|null $bar */
            public function foo($bar)
            {
            }

            /** @param ?string $quz */
            public function baz($quz)
            {
            }
        };
        $expectedTypes = [new Type('string', null, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'baz', 'quz'));
    }

    public function testGetParameterTypesForObjectTypeReturnsObjectTypes(): void
    {
        $object = new class() {
            /** @param Foo $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('object', 'Foo')];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesInfersTypedArrays(): void
    {
        $object = new class() {
            /** @param array $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesInfersTypedMixedArrays(): void
    {
        $object = new class() {
            /** @param mixed[] $bar */
            public function foo($bar)
            {
            }
        };
        // We cannot infer the key/value types from mixed arrays because each key/value might by a different type
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesInfersTypedObjectArrays(): void
    {
        $object = new class() {
            /** @param Closure[] $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('object', Closure::class))];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesInfersTypedScalarArrays(): void
    {
        $object = new class() {
            /** @param string[] $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesNormalizesPhpDocTypesToPhpTypes(): void
    {
        $object = new class() {
            /** @param boolean $foo */
            public function bool($foo)
            {
            }

            /** @param double $foo */
            public function float($foo)
            {
            }

            /** @param integer $foo */
            public function int($foo)
            {
            }
        };
        $this->assertEquals([new Type('bool')], $this->reflector->getParameterTypes(\get_class($object), 'bool', 'foo'));
        $this->assertEquals([new Type('float')], $this->reflector->getParameterTypes(\get_class($object), 'float', 'foo'));
        $this->assertEquals([new Type('int')], $this->reflector->getParameterTypes(\get_class($object), 'int', 'foo'));
    }

    public function testGetParameterTypesReturnsNullIfNoTypesCanBeFound(): void
    {
        $object = new class() {
            public function foo($bar)
            {
            }
        };
        $this->assertNull($this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
    }

    public function testGetParameterTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            /** @param string $bar */
            public function foo($bar)
            {
            }
        };
        $expectedTypes = [new Type('string')];
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
        // Technically, we're just manually making sure that the code paths are hit via code coverage
        $this->assertEquals($expectedTypes, $this->reflector->getParameterTypes(\get_class($object), 'foo', 'bar'));
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
            /** @var Foo<string, string> */
            public $foo;
        };
        $expectedTypes = [new Type('object', 'Foo', false, true, new Type('string'), new Type('string'))];
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

    public function testGetPropertyTypesForObjectTypeReturnsObjectTypes(): void
    {
        $object = new class() {
            /** @var Foo */
            public $foo;
        };
        $expectedTypes = [new Type('object', 'Foo')];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
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

    public function testGetPropertyTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            /** @var string */
            public $foo;
        };
        $expectedTypes = [new Type('string')];
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
        // Technically, we're just manually making sure that the code paths are hit via code coverage
        $this->assertEquals($expectedTypes, $this->reflector->getPropertyTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForClassWithoutThatMethodThrowsException(): void
    {
        $this->expectException(ReflectionException::class);
        $object = new class() {
        };
        $this->reflector->getReturnTypes(\get_class($object), 'foo');
    }

    public function testGetReturnTypesForCollectionReturnsTypesWithKeyAndValueTypesSet(): void
    {
        $object = new class() {
            /** @return Foo<string, string> */
            public function foo()
            {
            }
        };
        $expectedTypes = [new Type('object', 'Foo', false, true, new Type('string'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForCompoundTypeReturnsAllTypes(): void
    {
        $object = new class() {
            /** @return string|int */
            public function foo()
            {
            }
        };
        $expectedTypes = [
            new Type('string'),
            new Type('int')
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForNullableCompoundTypeReturnsNullableTypes(): void
    {
        $object = new class() {
            /** @return string|int|null */
            public function foo()
            {
            }
        };
        $expectedTypes = [
            new Type('string', null, true),
            new Type('int', null, true)
        ];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesForNullableTypeReturnsNullableTypes(): void
    {
        // Test both ways of declaring something nullable
        $object = new class() {
            /** @return string|null */
            public function foo()
            {
            }

            /** @return ?string */
            public function bar()
            {
            }
        };
        $expectedTypes = [new Type('string', null, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'bar'));
    }

    public function testGetReturnTypesForObjectTypeReturnsObjectTypes(): void
    {
        $object = new class() {
            /** @return Foo */
            public function foo()
            {
            }
        };
        $expectedTypes = [new Type('object', 'Foo')];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesInfersTypedArrays(): void
    {
        $object = new class() {
            /** @return array */
            public function foo()
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesInfersTypedMixedArrays(): void
    {
        $object = new class() {
            /** @return mixed[] */
            public function foo()
            {
            }
        };
        // We cannot infer the key/value types from mixed arrays because each key/value might by a different type
        $expectedTypes = [new Type('array', null, false, true)];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesInfersTypedObjectArrays(): void
    {
        $object = new class() {
            /** @return Closure[] */
            public function foo()
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('object', Closure::class))];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesInfersTypedScalarArrays(): void
    {
        $object = new class() {
            /** @return string[] */
            public function foo()
            {
            }
        };
        $expectedTypes = [new Type('array', null, false, true, new Type('int'), new Type('string'))];
        $this->assertEquals($expectedTypes, $this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesNormalizesPhpDocTypesToPhpTypes(): void
    {
        $object = new class() {
            /** @return boolean */
            public function bool()
            {
            }

            /** @return double */
            public function float()
            {
            }

            /** @return integer */
            public function int()
            {
            }
        };
        $this->assertEquals([new Type('bool')], $this->reflector->getReturnTypes(\get_class($object), 'bool'));
        $this->assertEquals([new Type('float')], $this->reflector->getReturnTypes(\get_class($object), 'float'));
        $this->assertEquals([new Type('int')], $this->reflector->getReturnTypes(\get_class($object), 'int'));
    }

    public function testGetReturnTypesReturnsNullIfNoTypesCanBeFound(): void
    {
        $object = new class() {
            public function foo()
            {
            }
        };
        $this->assertNull($this->reflector->getReturnTypes(\get_class($object), 'foo'));
    }

    public function testGetReturnTypesCachesResultsForNextTime(): void
    {
        $object = new class() {
            /** @return string */
            public function foo()
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
