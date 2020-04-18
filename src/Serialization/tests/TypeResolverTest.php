<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests;

use Aphiria\Serialization\Tests\Encoding\Mocks\User;
use Aphiria\Serialization\TypeResolver;
use PHPUnit\Framework\TestCase;

class TypeResolverTest extends TestCase
{
    public function testGettingArrayTypeReturnsNullForNonTypedArrays(): void
    {
        $this->assertNull(TypeResolver::getArrayType('string'));
        $this->assertNull(TypeResolver::getArrayType('[]'));
        $this->assertNull(TypeResolver::getArrayType('array'));
    }

    public function testGettingArrayTypeReturnsTypeTypedArrays(): void
    {
        $this->assertSame(User::class, TypeResolver::getArrayType(User::class . '[]'));
    }

    public function testResolvingEmptyArrayReturnsArrayType(): void
    {
        $this->assertEquals('array', TypeResolver::resolveType([]));
    }

    public function testResolvingNonEmptyArrayReturnsTypeOfFirstValue(): void
    {
        $this->assertEquals('string[]', TypeResolver::resolveType(['foo', 'bar']));
    }

    public function testResolvingTypeForObjectUsesObjectsClassName(): void
    {
        $this->assertSame(User::class, TypeResolver::resolveType(new User(123, 'foo@bar.com')));
    }

    public function testResolvingTypeForScalarUsesScalarType(): void
    {
        $this->assertEquals('boolean', TypeResolver::resolveType(true));
        $this->assertEquals('integer', TypeResolver::resolveType(1));
        $this->assertEquals('double', TypeResolver::resolveType(1.5));
        $this->assertEquals('string', TypeResolver::resolveType('foo'));
    }

    public function testTypeIsArrayReturnsTrueOnlyForArraysOfTypes(): void
    {
        $this->assertTrue(TypeResolver::typeIsArray('array'));
        $this->assertTrue(TypeResolver::typeIsArray(User::class . '[]'));
        $this->assertFalse(TypeResolver::typeIsArray('string'));
    }
}
