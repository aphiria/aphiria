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

class PhpDocTypeReflectorTest extends TestCase
{
    private PhpDocTypeReflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new PhpDocTypeReflector();
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
        $object = new class() {
            /** @var string|null */
            public $foo;
        };
        $expectedTypes = [new Type('string', null, true)];
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
