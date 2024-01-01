<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection\Tests;

use Aphiria\Reflection\Tests\Mocks\User;
use Aphiria\Reflection\TypeResolver;
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
        $this->assertSame('array', TypeResolver::resolveType([]));
    }

    public function testResolvingNonEmptyArrayReturnsTypeOfFirstValue(): void
    {
        $this->assertSame('string[]', TypeResolver::resolveType(['foo', 'bar']));
    }

    public function testResolvingTypeForObjectUsesObjectsClassName(): void
    {
        $this->assertSame(User::class, TypeResolver::resolveType(new User(123, 'foo@bar.com')));
    }

    public function testResolvingTypeForScalarUsesScalarType(): void
    {
        $this->assertSame('boolean', TypeResolver::resolveType(true));
        $this->assertSame('integer', TypeResolver::resolveType(1));
        $this->assertSame('double', TypeResolver::resolveType(1.5));
        $this->assertSame('string', TypeResolver::resolveType('foo'));
    }

    public function testTypeIsArrayReturnsTrueOnlyForArraysOfTypes(): void
    {
        $this->assertTrue(TypeResolver::typeIsArray('array'));
        $this->assertTrue(TypeResolver::typeIsArray(User::class . '[]'));
        $this->assertFalse(TypeResolver::typeIsArray('string'));
    }
}
