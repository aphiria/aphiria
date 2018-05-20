<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Normalizers;

use InvalidArgumentException;
use Opulence\Serialization\Normalizers\INormalizer;
use Opulence\Serialization\Normalizers\NormalizationException;
use Opulence\Serialization\Normalizers\ObjectNormalizer;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithArrayParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithNullableParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithTypedParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithTypedParamsAndNoGetters;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithTypedVariadicParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithUntypedOptionalParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithUntypedPararmsWithTypedGetters;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithUntypedScalars;
use Opulence\Serialization\Tests\Normalizers\Mocks\ConstructorWithUntypedVariadicParams;
use Opulence\Serialization\Tests\Normalizers\Mocks\DerivedClassWithProperties;
use Opulence\Serialization\Tests\Normalizers\Mocks\NoConstructor;
use Opulence\Serialization\Tests\Normalizers\Mocks\User;

/**
 * Tests the object normalizer
 */
class ObjectNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /** @var IParentNormalizer The parent normalizer */
    private $parentNormalizer;
    /** @var ObjectNormalizer The object normalizer */
    private $objectNormalizer;

    public function setUp(): void
    {
        $this->parentNormalizer = $this->createMock(INormalizer::class);
        $this->objectNormalizer = new ObjectNormalizer($this->parentNormalizer);
    }

    public function testDenormalizingClassWithArrayConstructorParamThrowsExceptionIfNormalizedValueIsNotArray(): void
    {
        $this->expectException(NormalizationException::class);
        $this->objectNormalizer->denormalize(['foo' => 'bar'], ConstructorWithArrayParams::class);
    }

    public function testDenormalizingClassWithArrayConstructorParamWorksIfNormalizedArrayContainsScalars(): void
    {
        $normalizedValue = ['foo' => ['bar', 'baz']];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with(['bar', 'baz'], 'string[]')
            ->willReturn(['bar', 'baz']);
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithArrayParams::class);
        $this->assertInstanceOf(ConstructorWithArrayParams::class, $value);
        $this->assertEquals(['bar', 'baz'], $value->getFoo());
    }

    public function testDenormalizingClassWithNoConstructorStillCreatesInstance(): void
    {
        $value = $this->objectNormalizer->denormalize([], NoConstructor::class);
        $this->assertInstanceOf(NoConstructor::class, $value);
    }

    public function testDenormalizingClassWithTypedConstructorParamsAndNoGettersDenormalizesByConstructorType(): void
    {
        $normalizedValue = ['foo' => 'dave', 'bar' => 'young'];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with('dave', 'string')
            ->willReturn('dave');
        $this->parentNormalizer->expects($this->at(1))
            ->method('denormalize')
            ->with('young', 'string')
            ->willReturn('young');
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithTypedParamsAndNoGetters::class);
        $this->assertInstanceOf(ConstructorWithTypedParamsAndNoGetters::class, $value);
    }

    public function testDenormalizingClassWithTypedConstructorParamsDenormalizesByConstructorType(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $normalizedValue = ['user' => ['id' => 123, 'email' => 'foo@bar.com']];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with(['id' => 123, 'email' => 'foo@bar.com'])
            ->willReturn($expectedUser);
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithTypedParams::class);
        $this->assertInstanceOf(ConstructorWithTypedParams::class, $value);
        $this->assertEquals($expectedUser, $value->getUser());
    }

    public function testDenormalizingClassWithTypedVariadicParamsDenormalizesByVariadicType(): void
    {
        $normalizedValue = ['users' => [['id' => 123, 'foo@bar.com'], ['id' => 456, 'email' => 'bar@baz.com']]];
        $expectedUsers = [new User(123, 'foo@bar.com'), new User(456, 'bar@baz.com')];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with($normalizedValue['users'], User::class . '[]')
            ->willReturn($expectedUsers);
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithTypedVariadicParams::class);
        $this->assertInstanceOf(ConstructorWithTypedVariadicParams::class, $value);
        $this->assertEquals($expectedUsers, $value->getUsers());
    }

    public function testDenormalizingClassWithUntypedConstructorParamsAndUntypedGettersStillWorksIfNormalizedValuesAreScalars(): void
    {
        $normalizedValue = ['foo' => 123, 'bar' => 456];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with(123, 'integer')
            ->willReturn(123);
        $this->parentNormalizer->expects($this->at(1))
            ->method('denormalize')
            ->with(456, 'integer')
            ->willReturn(456);
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithUntypedScalars::class);
        $this->assertInstanceOf(ConstructorWithUntypedScalars::class, $value);
        $this->assertEquals(123, $value->getFoo());
        $this->assertEquals(456, $value->getBar());
    }

    public function testDenormalizingClassWithUntypedConstructorParamsUsesGetterTypes(): void
    {
        $normalizedValue = ['foo' => ['id' => 123, 'email' => 'foo@bar.com'], 'bar' => true, 'baz' => true];
        $expectedUser = new User(123, 'foo@bar.com');
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with($normalizedValue['foo'], User::class)
            ->willReturn($expectedUser);
        $this->parentNormalizer->expects($this->at(1))
            ->method('denormalize')
            ->with(true, 'bool')
            ->willReturn(true);
        $this->parentNormalizer->expects($this->at(2))
            ->method('denormalize')
            ->with(true, 'bool')
            ->willReturn(true);
        $value = $this->objectNormalizer->denormalize(
            $normalizedValue,
            ConstructorWithUntypedPararmsWithTypedGetters::class
        );
        $this->assertInstanceOf(ConstructorWithUntypedPararmsWithTypedGetters::class, $value);
        $this->assertEquals($expectedUser, $value->getFoo());
        $this->assertTrue($value->isBar());
        $this->assertTrue($value->hasBaz());
    }

    public function testDenormalizingClassWitVariadicConstructorParamThrowsExceptionIfNormalizedValueIsNotArray(): void
    {
        $this->expectException(NormalizationException::class);
        $this->objectNormalizer->denormalize(['foo' => 'bar'], ConstructorWithUntypedVariadicParams::class);
    }

    public function testDenormalizingClassWithUntypedVariadicParamsDenormalizesByNormalizedValueType(): void
    {
        $normalizedValue = ['foo' => ['bar', 'baz']];
        $this->parentNormalizer->expects($this->at(0))
            ->method('denormalize')
            ->with($normalizedValue['foo'], 'string[]')
            ->willReturn($normalizedValue['foo']);
        $value = $this->objectNormalizer->denormalize($normalizedValue, ConstructorWithUntypedVariadicParams::class);
        $this->assertInstanceOf(ConstructorWithUntypedVariadicParams::class, $value);
        $this->assertEquals(['bar', 'baz'], $value->getFoo());
    }

    public function testDenormalizingHashWithMissingPropertyStillWorksIfConstructorParamIsNullable(): void
    {
        $value = $this->objectNormalizer->denormalize([], ConstructorWithNullableParams::class);
        $this->assertInstanceOf(ConstructorWithNullableParams::class, $value);
        $this->assertNull($value->getFoo());
    }

    public function testDenormalizingHashMissingPropertyStillWorksIfUntypedConstructorParamIsOptional(): void
    {
        $value = $this->objectNormalizer->denormalize([], ConstructorWithUntypedOptionalParams::class);
        $this->assertInstanceOf(ConstructorWithUntypedOptionalParams::class, $value);
        $this->assertSame(1, $value->getFoo());
    }

    public function testDenormalizingHashMissingRequiredPropertyThrowsException(): void
    {
        $this->expectException(NormalizationException::class);
        $this->objectNormalizer->denormalize([], ConstructorWithTypedParams::class);
    }

    public function testDenormalizingNonArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->objectNormalizer->denormalize('foo', 'bar');
    }

    public function testNormalizingCreatesHashFromPropertiesOfClass(): void
    {
        $value = new ConstructorWithTypedParamsAndNoGetters('dave', 'young');
        $this->parentNormalizer->expects($this->at(0))
            ->method('normalize')
            ->with('dave')
            ->willReturn('dave');
        $this->parentNormalizer->expects($this->at(1))
            ->method('normalize')
            ->with('young')
            ->willReturn('young');
        $this->assertEquals(['foo' => 'dave', 'bar' => 'young'], $this->objectNormalizer->normalize($value));
    }

    public function testNormalizingDerivedClassIncludesPropertiesFromBaseClass(): void
    {
        $value = new DerivedClassWithProperties('dave', 'young');
        // Base class properties come first, which is why things are in the order they are
        $this->parentNormalizer->expects($this->at(0))
            ->method('normalize')
            ->with('young')
            ->willReturn('young');
        $this->parentNormalizer->expects($this->at(1))
            ->method('normalize')
            ->with('dave')
            ->willReturn('dave');
        $this->assertEquals(['bar' => 'young', 'foo' => 'dave'], $this->objectNormalizer->normalize($value));
    }

    public function testNormalizingNonObjectThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->objectNormalizer->normalize([]);
    }
}
