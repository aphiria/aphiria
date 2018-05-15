<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\EncodingException;
use Opulence\Serialization\Encoding\IEncoder;
use Opulence\Serialization\Encoding\IEncodingInterceptor;
use Opulence\Serialization\Encoding\Property;
use Opulence\Serialization\JsonSerializer;
use Opulence\Serialization\SerializationException;
use Opulence\Serialization\Tests\Mocks\User;

/**
 * Tests the JSON serializer
 */
class JsonSerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonSerializer The serializer to use in tests */
    private $serializer;
    /** @var EncoderRegistry The encoder registry to use in tests */
    private $encoders;

    public function setUp(): void
    {
        $this->encoders = new EncoderRegistry();
        $this->encoders->registerObjectEncoder(
            User::class,
            function ($hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->serializer = new JsonSerializer($this->encoders);
    }

    public function testDeserializingArrayDecodesEachValueUsingEncoder(): void
    {
        $expectedUsers = [
            new User(123, 'foo@bar.com'),
            new User(456, 'bar@baz.com')
        ];
        $encodedUsers = '[{"id":123,"email":"foo@bar.com"},{"id":456,"email":"bar@baz.com"}]';
        $actualUsers = $this->serializer->deserialize($encodedUsers, User::class, true);
        $this->assertEquals($expectedUsers, $actualUsers);
    }

    public function testDeserializingEmptyArrayReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->serializer->deserialize('[]', User::class, true));
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"', self::class);
    }

    public function testDeserializingNullReturnsNull(): void
    {
        $this->assertNull($this->serializer->deserialize('null', 'string'));
    }

    public function testDeserializingTypeCreatesInstanceOfTypeFromEncoder(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals($user, $this->serializer->deserialize('{"id":123,"email":"foo@bar.com"}', User::class));
    }

    public function testDeserializingValueSendEncoderThroughInterceptors(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IEncodingInterceptor $encodingInterceptor */
        $encodingInterceptor = $this->createMock(IEncodingInterceptor::class);
        // Note: The inteceptor will fip the ID and email values around
        $encodingInterceptor->expects($this->at(0))
            ->method('onPreDecoding')
            ->with(321, 'int')
            ->willReturn(321);
        $encodingInterceptor->expects($this->at(1))
            ->method('onPreDecoding')
            ->with('bar@foo.com', 'string')
            ->willReturn('bar@foo.com');
        $encodingInterceptor->expects($this->at(2))
            ->method('onPreDecoding')
            ->with(['id' => 321, 'email' => 'bar@foo.com'], User::class)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $serializer = new JsonSerializer($this->encoders, [$encodingInterceptor]);
        $this->assertEquals($user, $serializer->deserialize('{"id":321,"email":"bar@foo.com"}', User::class));
    }

    public function testEncodingExceptionThrownDuringDeserializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        /** @var IEncoder $encoder */
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('getType')
            ->willReturn('foo');
        $encoder->expects($this->once())
            ->method('decode')
            ->will($this->throwException(new EncodingException));
        $this->encoders->registerEncoder($encoder);
        $this->serializer->deserialize('{"foo":"bar"}', 'foo');
    }

    public function testEncodingExceptionThrownDuringSerializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        // Purposely overwrite encoder for string so we can easily test throwing an exception
        /** @var IEncoder $encoder */
        $encoder = $this->createMock(IEncoder::class);
        $encoder->expects($this->once())
            ->method('getType')
            ->willReturn('string');
        $encoder->expects($this->once())
            ->method('encode')
            ->will($this->throwException(new EncodingException));
        $this->encoders->registerEncoder($encoder);
        $this->serializer->serialize('foo');
    }

    public function testSerializingArrayEncodesEachValueUsingEncoder(): void
    {
        $users = [
            new User(123, 'foo@bar.com'),
            new User(456, 'bar@baz.com')
        ];
        $expectedEncodedUsers = '[{"id":123,"email":"foo@bar.com"},{"id":456,"email":"bar@baz.com"}]';
        $actualEncodedUsers = $this->serializer->serialize($users);
        $this->assertEquals($expectedEncodedUsers, $actualEncodedUsers);
    }

    public function testSerializingEmptyArrayReturnsEmptyArray(): void
    {
        $this->assertEquals('[]', $this->serializer->serialize([]));
    }

    public function testSerializingNullReturnsNull(): void
    {
        $this->assertEquals('null', $this->serializer->serialize(null));
    }

    public function testSerializingValueSendsEncoderThroughInterceptors(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IEncodingInterceptor $encodingInterceptor */
        $encodingInterceptor = $this->createMock(IEncodingInterceptor::class);
        $encodingInterceptor->expects($this->at(0))
            ->method('onPostEncoding')
            ->with(123, 'int')
            ->willReturn(123);
        $encodingInterceptor->expects($this->at(1))
            ->method('onPostEncoding')
            ->with('foo@bar.com', 'string')
            ->willReturn('foo@bar.com');
        $encodingInterceptor->expects($this->at(2))
            ->method('onPostEncoding')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn(['_id_' => 123, '_email_' => 'foo@bar.com']);
        $serializer = new JsonSerializer($this->encoders, [$encodingInterceptor]);
        $this->assertEquals('{"_id_":123,"_email_":"foo@bar.com"}', $serializer->serialize($user));
    }

    public function testSerializingValueJsonEncodesItsEncoder(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals('{"id":123,"email":"foo@bar.com"}', $this->serializer->serialize($user));
    }
}
