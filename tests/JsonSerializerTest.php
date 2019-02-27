<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Tests;

use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Serialization\Encoding\EncodingException;
use Aphiria\Serialization\Encoding\IEncoder;
use Aphiria\Serialization\JsonSerializer;
use Aphiria\Serialization\SerializationException;
use Aphiria\Serialization\Tests\Encoding\Mocks\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests the JSON serializer
 */
class JsonSerializerTest extends TestCase
{
    /** @var JsonSerializer The serializer to use in tests */
    private $serializer;
    /** @var EncoderRegistry The encoder registry to use in tests */
    private $encoders;

    protected function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->serializer = new JsonSerializer($this->encoders);
    }

    public function testDeserializingValueConvertsJsonToDecodedValue(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $encodedUser = '{"id":123,"email":"foo@bar.com"}';
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('decode')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn($expectedUser);
        $this->encoders->registerEncoder(User::class, $encoder);
        $this->assertSame($expectedUser, $this->serializer->deserialize($encodedUser, User::class));
    }

    public function testSerializingValueConvertsEncodedValueToJson(): void
    {
        $user = new User(123, 'foo@bar.com');
        $expectedSerializedUser = '{"id":123,"email":"foo@bar.com"}';
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $this->encoders->registerEncoder(User::class, $encoder);
        $this->assertEquals($expectedSerializedUser, $this->serializer->serialize($user));
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to deserialize value');
        $this->serializer->deserialize('"', self::class);
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
        $this->serializer->deserialize('{"foo":"bar"}', 'foo');
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

    public function testSerializeThrowSerializationExceptionDuringJsonEncoding(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to serialize value');
        $this->serializer->serialize(123456);
    }
}
