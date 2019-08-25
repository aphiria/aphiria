<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\EncodingContext;
use Aphiria\Serialization\Encoding\EncodingException;
use Aphiria\Serialization\Encoding\IEncoder;
use Aphiria\Serialization\Encoding\IPropertyNameFormatter;
use Aphiria\Serialization\Encoding\ObjectEncoder;
use Aphiria\Serialization\Encoding\ScalarEncoder;
use Aphiria\Serialization\Tests\Encoding\Mocks\CircularReferenceA;
use Aphiria\Serialization\Tests\Encoding\Mocks\CircularReferenceB;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithArrayParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithNullableParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithTypedParamAndPublicProperty;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithTypedParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithTypedParamsAndNoGetters;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithTypedVariadicParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUnsetTypedPublicProperty;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedOptionalParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedParamAndTypedPropertiesAndNoGetters;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedParamAndTypedPropertyAndTypedGetter;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedParamsWithTypedGetters;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedScalars;
use Aphiria\Serialization\Tests\Encoding\Mocks\ConstructorWithUntypedVariadicParams;
use Aphiria\Serialization\Tests\Encoding\Mocks\DerivedClassWithProperties;
use Aphiria\Serialization\Tests\Encoding\Mocks\NoConstructor;
use Aphiria\Serialization\Tests\Encoding\Mocks\PublicArrayProperty;
use Aphiria\Serialization\Tests\Encoding\Mocks\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object encoder
 */
class ObjectEncoderTest extends TestCase
{
    private EncoderRegistry $encoders;
    private ObjectEncoder $objectEncoder;

    protected function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->objectEncoder = new ObjectEncoder($this->encoders);
    }

    public function testDecodingClassWithArrayConstructorParamThrowsExceptionIfEncodedValueIsNotArray(): void
    {
        $this->expectException(EncodingException::class);
        $this->expectExceptionMessage('Value must be an array');
        $this->objectEncoder->decode(['foo' => 'bar'], ConstructorWithArrayParams::class, new EncodingContext());
    }

    public function testDecodingClassWithArrayConstructorParamWorksIfEncodedArrayContainsScalars(): void
    {
        $context = new EncodingContext();
        $encodedValue = ['foo' => ['bar', 'baz']];
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with(['bar', 'baz'], 'string[]', $context)
            ->willReturn(['bar', 'baz']);
        $this->encoders->registerEncoder('array', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithArrayParams::class, $context);
        $this->assertInstanceOf(ConstructorWithArrayParams::class, $value);
        $this->assertEquals(['bar', 'baz'], $value->getFoo());
    }

    public function testDecodingClassWithArrayPublicPropertyThrowsExceptionIfEncodedValueIsNotArray(): void
    {
        $this->expectException(EncodingException::class);
        $this->expectExceptionMessage('Value must be an array');
        $this->objectEncoder->decode(['foo' => 'bar'], PublicArrayProperty::class, new EncodingContext());
    }

    public function testDecodingClassWithArrayPublicPropertyWorksIfEncodedArrayContainsScalars(): void
    {
        $context = new EncodingContext();
        $encodedValue = ['foo' => ['bar', 'baz']];
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with(['bar', 'baz'], 'string[]', $context)
            ->willReturn(['bar', 'baz']);
        $this->encoders->registerEncoder('array', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, PublicArrayProperty::class, $context);
        $this->assertInstanceOf(PublicArrayProperty::class, $value);
        $this->assertEquals(['bar', 'baz'], $value->foo);
    }

    public function testDecodingClassWithNoConstructorParamsButWithTypedPropertyDecodesByPropertyType(): void
    {
        $encodedValue = ['foo' => '123'];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with('123', 'int', $context)
            ->willReturn(123);
        $this->encoders->registerEncoder('int', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithUnsetTypedPublicProperty::class, $context);
        $this->assertInstanceOf(ConstructorWithUnsetTypedPublicProperty::class, $value);
        $this->assertEquals(123, $value->foo);
    }

    public function testDecodingClassWithNoConstructorStillCreatesInstance(): void
    {
        $value = $this->objectEncoder->decode([], NoConstructor::class, new EncodingContext());
        $this->assertInstanceOf(NoConstructor::class, $value);
    }

    public function testDecodingClassWithPublicPropertySetsPropertyAfterInstantiation(): void
    {
        $encodedValue = ['foo' => 'dave', 'bar' => 'young'];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with('young', 'string', $context)
            ->willReturn('young');
        $encoder->expects($this->at(1))
            ->method('decode')
            ->with('dave', 'string', $context)
            ->willReturn('dave');
        $this->encoders->registerEncoder('string', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithTypedParamAndPublicProperty::class, $context);
        $this->assertInstanceOf(ConstructorWithTypedParamAndPublicProperty::class, $value);
        $this->assertEquals('dave', $value->foo);
        $this->assertEquals('young', $value->getBar());
    }

    public function testDecodingClassWithTypedConstructorParamsAndNoGettersDecodesByConstructorType(): void
    {
        $encodedValue = ['foo' => 'dave', 'bar' => 'young'];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with('dave', 'string', $context)
            ->willReturn('dave');
        $encoder->expects($this->at(1))
            ->method('decode')
            ->with('young', 'string')
            ->willReturn('young');
        $this->encoders->registerEncoder('string', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithTypedParamsAndNoGetters::class, $context);
        $this->assertInstanceOf(ConstructorWithTypedParamsAndNoGetters::class, $value);
    }

    public function testDecodingClassWithTypedConstructorParamsDecodesByConstructorType(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $encodedValue = ['user' => ['id' => 123, 'email' => 'foo@bar.com']];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class, $context)
            ->willReturn($expectedUser);
        $this->encoders->registerEncoder(User::class, $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithTypedParams::class, $context);
        $this->assertInstanceOf(ConstructorWithTypedParams::class, $value);
        $this->assertEquals($expectedUser, $value->getUser());
    }

    public function testDecodingClassWithTypedVariadicParamsDecodesByVariadicType(): void
    {
        $encodedValue = ['users' => [['id' => 123, 'foo@bar.com'], ['id' => 456, 'email' => 'bar@baz.com']]];
        $expectedUsers = [new User(123, 'foo@bar.com'), new User(456, 'bar@baz.com')];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with($encodedValue['users'], User::class . '[]', $context)
            ->willReturn($expectedUsers);
        $this->encoders->registerEncoder('array', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithTypedVariadicParams::class, $context);
        $this->assertInstanceOf(ConstructorWithTypedVariadicParams::class, $value);
        $this->assertEquals($expectedUsers, $value->getUsers());
    }

    public function testDecodingClassWithUntypedConstructorParamsAndTypedPropertiesAndTypedGettersDecodesByPropertyType(): void
    {
        $encodedValue = ['foo' => 'bar'];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with('bar', 'string', $context)
            ->willReturn('bar');
        $this->encoders->registerEncoder('string', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithUntypedParamAndTypedPropertyAndTypedGetter::class, $context);
        $this->assertInstanceOf(ConstructorWithUntypedParamAndTypedPropertyAndTypedGetter::class, $value);
        $this->assertEquals('bar', $value->foo);
    }

    public function testDecodingClassWithUntypedConstructorParamsAndTypedPropertiesAndNoGettersDecodesByPropertyType(): void
    {
        $encodedValue = ['foo' => '123'];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with('123', 'int', $context)
            ->willReturn(123);
        $this->encoders->registerEncoder('int', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithUntypedParamAndTypedPropertiesAndNoGetters::class, $context);
        $this->assertInstanceOf(ConstructorWithUntypedParamAndTypedPropertiesAndNoGetters::class, $value);
        $this->assertEquals(123, $value->foo);
    }

    public function testDecodingClassWithUntypedConstructorParamsAndUntypedGettersStillWorksIfEncodedValuesAreScalars(): void
    {
        $encodedValue = ['foo' => 123, 'bar' => 456];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with(123, 'integer', $context)
            ->willReturn(123);
        $encoder->expects($this->at(1))
            ->method('decode')
            ->with(456, 'integer', $context)
            ->willReturn(456);
        $this->encoders->registerEncoder('integer', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithUntypedScalars::class, $context);
        $this->assertInstanceOf(ConstructorWithUntypedScalars::class, $value);
        $this->assertEquals(123, $value->getFoo());
        $this->assertEquals(456, $value->getBar());
    }

    public function testDecodingClassWithUntypedConstructorParamsUsesGetterTypes(): void
    {
        $encodedValue = ['foo' => ['id' => 123, 'email' => 'foo@bar.com'], 'bar' => true, 'baz' => true];
        $expectedUser = new User(123, 'foo@bar.com');
        $context = new EncodingContext();
        $userEncoder = $this->createMock(IEncoder::class);
        $userEncoder->expects($this->at(0))
            ->method('decode')
            ->with($encodedValue['foo'], User::class, $context)
            ->willReturn($expectedUser);
        $this->encoders->registerEncoder(User::class, $userEncoder);
        $boolEncoder = $this->createMock(IEncoder::class);
        $boolEncoder->expects($this->at(0))
            ->method('decode')
            ->with(true, 'bool', $context)
            ->willReturn(true);
        $boolEncoder->expects($this->at(1))
            ->method('decode')
            ->with(true, 'bool', $context)
            ->willReturn(true);
        $this->encoders->registerEncoder('bool', $boolEncoder);
        $value = $this->objectEncoder->decode(
            $encodedValue,
            ConstructorWithUntypedParamsWithTypedGetters::class,
            $context
        );
        $this->assertInstanceOf(ConstructorWithUntypedParamsWithTypedGetters::class, $value);
        $this->assertEquals($expectedUser, $value->getFoo());
        $this->assertTrue($value->isBar());
        $this->assertTrue($value->hasBaz());
    }

    public function testDecodingClassWitVariadicConstructorParamThrowsExceptionIfEncodedValueIsNotArray(): void
    {
        $this->expectException(EncodingException::class);
        $this->expectExceptionMessage('Value must be an array');
        $this->objectEncoder->decode(
            ['foo' => 'bar'],
            ConstructorWithUntypedVariadicParams::class,
            new EncodingContext()
        );
    }

    public function testDecodingClassWithUntypedVariadicParamsDecodesByEncodedValueType(): void
    {
        $encodedValue = ['foo' => ['bar', 'baz']];
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('decode')
            ->with($encodedValue['foo'], 'string[]', $context)
            ->willReturn($encodedValue['foo']);
        $this->encoders->registerEncoder('array', $encoder);
        $value = $this->objectEncoder->decode($encodedValue, ConstructorWithUntypedVariadicParams::class, $context);
        $this->assertInstanceOf(ConstructorWithUntypedVariadicParams::class, $value);
        $this->assertEquals(['bar', 'baz'], $value->getFoo());
    }

    public function testDecodingHashWithMissingPropertyStillWorksIfConstructorParamIsNullable(): void
    {
        $value = $this->objectEncoder->decode([], ConstructorWithNullableParams::class, new EncodingContext());
        $this->assertInstanceOf(ConstructorWithNullableParams::class, $value);
        $this->assertNull($value->getFoo());
    }

    public function testDecodingHashMissingPropertyStillWorksIfUntypedConstructorParamIsOptional(): void
    {
        $value = $this->objectEncoder->decode([], ConstructorWithUntypedOptionalParams::class, new EncodingContext());
        $this->assertInstanceOf(ConstructorWithUntypedOptionalParams::class, $value);
        $this->assertSame(1, $value->getFoo());
    }

    public function testDecodingHashMissingRequiredPropertyThrowsException(): void
    {
        $this->expectException(EncodingException::class);
        $this->expectExceptionMessage('No value specified for parameter "user"');
        $this->objectEncoder->decode([], ConstructorWithTypedParams::class, new EncodingContext());
    }

    public function testDecodingNonArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type bar is not a valid class name');
        $this->objectEncoder->decode('foo', 'bar', new EncodingContext());
    }

    public function testEncodingCircularReferenceThrowsException(): void
    {
        $this->expectException(EncodingException::class);
        $this->expectExceptionMessage('Circular reference detected');
        $a = new CircularReferenceA();
        $b = new CircularReferenceB($a);
        $a->setFoo($b);
        $this->encoders->registerDefaultObjectEncoder($this->objectEncoder);
        $this->objectEncoder->encode($a, new EncodingContext());
    }

    public function testEncodingCreatesHashFromPropertiesOfClass(): void
    {
        $value = new ConstructorWithTypedParamsAndNoGetters('dave', 'young');
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('encode')
            ->with('dave', $context)
            ->willReturn('dave');
        $encoder->expects($this->at(1))
            ->method('encode')
            ->with('young', $context)
            ->willReturn('young');
        $this->encoders->registerEncoder('string', $encoder);
        $this->assertEquals(['foo' => 'dave', 'bar' => 'young'], $this->objectEncoder->encode($value, $context));
    }

    public function testEncodingDerivedClassIncludesPropertiesFromBaseClass(): void
    {
        $value = new DerivedClassWithProperties('dave', 'young');
        // Base class properties come first, which is why things are in the order they are
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('encode')
            ->with('young', $context)
            ->willReturn('young');
        $encoder->expects($this->at(1))
            ->method('encode')
            ->with('dave', $context)
            ->willReturn('dave');
        $this->encoders->registerEncoder('string', $encoder);
        $this->assertEquals(['bar' => 'young', 'foo' => 'dave'], $this->objectEncoder->encode($value, $context));
    }

    public function testEncodingDoesNotIncludeIgnoredProperty(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->objectEncoder->addIgnoredProperty(User::class, 'email');
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('encode')
            ->with(123, $context)
            ->willReturn(123);
        $this->encoders->registerEncoder('integer', $encoder);
        $this->assertEquals(['id' => 123], $this->objectEncoder->encode($user, $context));
    }

    public function testEncodingDoesNotIncludeMultipleIgnoredProperties(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->objectEncoder->addIgnoredProperty(User::class, ['email']);
        $context = new EncodingContext();
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->at(0))
            ->method('encode')
            ->with(123, $context)
            ->willReturn(123);
        $this->encoders->registerEncoder('integer', $encoder);
        $this->assertEquals(['id' => 123], $this->objectEncoder->encode($user, $context));
    }

    public function testEncodingFormatsPropertyNameFormatterIfOneIsSpecified(): void
    {
        /** @var IPropertyNameFormatter|PHPUnit_Framework_MockObject_MockObject $propertyNameFormatter */
        $propertyNameFormatter = $this->createMock(IPropertyNameFormatter::class);
        $objectEncoder = new ObjectEncoder($this->encoders, $propertyNameFormatter);
        $this->encoders->registerDefaultScalarEncoder(new ScalarEncoder());
        $propertyNameFormatter->expects($this->at(0))
            ->method('formatPropertyName')
            ->with('id')
            ->willReturn('_id');
        $propertyNameFormatter->expects($this->at(1))
            ->method('formatPropertyName')
            ->with('email')
            ->willReturn('_email');
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals(
            ['_id' => 123, '_email' => 'foo@bar.com'],
            $objectEncoder->encode($user, new EncodingContext())
        );
    }

    public function testEncodingNonObjectThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be an object');
        $this->objectEncoder->encode([], new EncodingContext());
    }

    public function testIgnoringPropertyNameThatIsNotStringOrArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property name must be a string or array of strings');
        $this->objectEncoder->addIgnoredProperty(User::class, $this);
    }

    public function testDecodingWithNonArrayObjectHashShouldThrowInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be an associative array');
        $this->objectEncoder->decode(12345, 'InvalidArgumentException', new EncodingContext());
    }
}
