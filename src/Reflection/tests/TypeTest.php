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

use Aphiria\Reflection\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function getIsPhpTypeTests(): array
    {
        return [
            ['array', true],
            ['bool', true],
            ['callable', true],
            ['float', true],
            ['int', true],
            ['mixed', true],
            ['null', true],
            ['object', true],
            ['resource', true],
            ['string', true],
            [self::class, false]
        ];
    }

    public function testGetPhpTypeReturnsPhpType(): void
    {
        $type = new Type('int');
        $this->assertEquals('int', $type->getPhpType());
    }

    public function testGetClassReturnsClassForClassType(): void
    {
        $type = new Type('object', self::class);
        $this->assertEquals(self::class, $type->getClass());
    }

    public function testGetClassReturnsNullForNonClassType(): void
    {
        $type = new Type('int');
        $this->assertNull($type->getClass());
    }

    public function testGetIterableKeyTypeReturnsNullIfTypeIsNotIterable(): void
    {
        $type = new Type('int');
        $this->assertNull($type->getIterableKeyType());
    }

    public function testGetIterableKeyTypeReturnsTypeIfTypeIsIterable(): void
    {
        $keyType = new Type('int');
        $valueType = new Type('string');
        $type = new Type('array', null, false, true, $keyType, $valueType);
        $this->assertSame($keyType, $type->getIterableKeyType());
    }

    public function testGetIterableValueTypeReturnsNullIfTypeIsNotIterable(): void
    {
        $type = new Type('int');
        $this->assertNull($type->getIterableValueType());
    }

    public function testGetIterableValueTypeReturnsTypeIfTypeIsIterable(): void
    {
        $keyType = new Type('int');
        $valueType = new Type('string');
        $type = new Type('array', null, false, true, $keyType, $valueType);
        $this->assertSame($valueType, $type->getIterableValueType());
    }

    public function testIsClassReturnsWhetherOrNotTypeIsClass(): void
    {
        $classType = new Type('object', 'Foo');
        $this->assertTrue($classType->isClass());
        $nonClassType = new Type('int');
        $this->assertFalse($nonClassType->isClass());
    }

    public function testIsIterableReturnsWhetherOrNotTypeIsIterable(): void
    {
        $iterableType = new Type('array', null, false, true, new Type('int'), new Type('string'));
        $this->assertTrue($iterableType->isIterable());
        $nonIterableType = new Type('int');
        $this->assertFalse($nonIterableType->isIterable());
    }

    public function testIsNullableReturnsWhetherOrNotTypeIsNullable(): void
    {
        $nullableType = new Type('int', null, true);
        $this->assertTrue($nullableType->isNullable());
        $nonNullableType = new Type('int');
        $this->assertFalse($nonNullableType->isNullable());
    }

    /**
     * @param string $type The type to test
     * @param bool $expectedValue The expected value
     * @dataProvider getIsPhpTypeTests
     */
    public function testIsPhpTypeReturnsTrueForPhpTypes(string $type, bool $expectedValue): void
    {
        $this->assertEquals($expectedValue, Type::isPhpType($type));
    }
}
