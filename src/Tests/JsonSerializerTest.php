<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use Opulence\Serialization\Encoding\EncodingException;
use Opulence\Serialization\Encoding\IEncoder;
use Opulence\Serialization\JsonSerializer;
use Opulence\Serialization\SerializationException;
use Opulence\Serialization\Tests\Encoding\Mocks\User;

/**
 * Tests the JSON serializer
 */
class JsonSerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonSerializer The serializer to use in tests */
    private $serializer;
    /** @var IEncoder The encoder to use in tests */
    private $encoder;

    public function setUp(): void
    {
        $this->encoder = $this->createMock(IEncoder::class);
        $this->serializer = new JsonSerializer($this->encoder);
    }

    public function testDeserializingValueConvertsJsonToDecodedValue(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $encodedUser = '{"id":123,"email":"foo@bar.com"}';
        $this->encoder->expects($this->once())
            ->method('decode')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn($expectedUser);
        $this->assertSame($expectedUser, $this->serializer->deserialize($encodedUser, User::class));
    }

    public function testSerializingValueConvertsEncodedValueToJson(): void
    {
        $user = new User(123, 'foo@bar.com');
        $expectedSerializedUser = '{"id":123,"email":"foo@bar.com"}';
        $this->encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $this->assertEquals($expectedSerializedUser, $this->serializer->serialize($user));
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"', self::class);
    }

    public function testEncodingExceptionThrownDuringDeserializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        $this->encoder->expects($this->once())
            ->method('decode')
            ->with(['foo' => 'bar'], 'foo')
            ->will($this->throwException(new EncodingException));
        $this->serializer->deserialize('{"foo":"bar"}', 'foo');
    }

    public function testEncodingExceptionThrownDuringSerializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        $user = new User(123, 'foo@bar.com');
        $this->encoder->expects($this->once())
            ->method('encode')
            ->with($user)
            ->will($this->throwException(new EncodingException));
        $this->serializer->serialize($user);
    }
}
