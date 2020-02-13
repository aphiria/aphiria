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

use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\EncodingException;
use Aphiria\Serialization\Encoding\IEncoder;
use Aphiria\Serialization\FormUrlEncodedSerializer;
use Aphiria\Serialization\SerializationException;
use Aphiria\Serialization\Tests\Encoding\Mocks\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests the form URL-encoded serializer
 */
class FormUrlEncodedSerializerTest extends TestCase
{
    private FormUrlEncodedSerializer $serializer;
    private EncoderRegistry $encoders;

    protected function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->serializer = new FormUrlEncodedSerializer($this->encoders);
    }

    public function testDeserializingValueConvertsFormUrlEncodedValueToDecodedValue(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $encodedUser = 'id=123&email=foo%40bar.com';
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('decode')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn($expectedUser);
        $this->encoders->registerEncoder(User::class, $encoder);
        $this->assertSame($expectedUser, $this->serializer->deserialize($encodedUser, User::class));
    }

    public function testSerializingValueConvertsEncodedValueToFormUrlEncodedValue(): void
    {
        $user = new User(123, 'foo@bar.com');
        $expectedSerializedUser = 'id=123&email=foo%40bar.com';
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $this->encoders->registerEncoder(User::class, $encoder);
        $this->assertEquals($expectedSerializedUser, $this->serializer->serialize($user));
    }

    public function testEncodingExceptionThrownDuringDeserializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to deserialize value');
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('decode')
            ->with(['foo' => 'bar'], 'foo')
            ->will($this->throwException(new EncodingException));
        $this->encoders->registerEncoder('foo', $encoder);
        $this->serializer->deserialize('foo=bar', 'foo');
    }

    public function testEncodingExceptionThrownDuringSerializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to serialize value');
        $user = new User(123, 'foo@bar.com');
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->will($this->throwException(new EncodingException));
        $this->encoders->registerEncoder(User::class, $encoder);
        $this->serializer->serialize($user);
    }
}
