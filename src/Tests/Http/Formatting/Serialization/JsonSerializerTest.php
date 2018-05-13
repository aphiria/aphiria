<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\Encoding\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\Encoding\EncodingException;
use Opulence\Net\Http\Formatting\Serialization\Encoding\IContract;
use Opulence\Net\Http\Formatting\Serialization\Encoding\IEncodingInterceptor;
use Opulence\Net\Http\Formatting\Serialization\Encoding\Property;
use Opulence\Net\Http\Formatting\Serialization\JsonSerializer;
use Opulence\Net\Http\Formatting\Serialization\SerializationException;
use Opulence\Net\Tests\Http\Formatting\Serialization\Mocks\User;

/**
 * Tests the JSON serializer
 */
class JsonSerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonSerializer The serializer to use in tests */
    private $serializer;
    /** @var ContractRegistry The contract registry to use in tests */
    private $contracts;

    public function setUp(): void
    {
        $this->contracts = new ContractRegistry();
        $this->contracts->registerObjectContract(
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
        $this->serializer = new JsonSerializer($this->contracts);
    }

    public function testDeserializingArrayDecodesEachValueUsingContract(): void
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

    public function testDeserializingTypeCreatesInstanceOfTypeFromContract(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals($user, $this->serializer->deserialize('{"id":123,"email":"foo@bar.com"}', User::class));
    }

    public function testDeserializingValueSendContractThroughInterceptors(): void
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
        $serializer = new JsonSerializer($this->contracts, [$encodingInterceptor]);
        $this->assertEquals($user, $serializer->deserialize('{"id":321,"email":"bar@foo.com"}', User::class));
    }

    public function testEncodingExceptionThrownDuringDeserializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        /** @var IContract $contract */
        $contract = $this->createMock(IContract::class);
        $contract->expects($this->once())
            ->method('getType')
            ->willReturn('foo');
        $contract->expects($this->once())
            ->method('decode')
            ->will($this->throwException(new EncodingException));
        $this->contracts->registerContract($contract);
        $this->serializer->deserialize('{"foo":"bar"}', 'foo');
    }

    public function testEncodingExceptionThrownDuringSerializationIsRethrown(): void
    {
        $this->expectException(SerializationException::class);
        // Purposely overwrite contract for string so we can easily test throwing an exception
        /** @var IContract $contract */
        $contract = $this->createMock(IContract::class);
        $contract->expects($this->once())
            ->method('getType')
            ->willReturn('string');
        $contract->expects($this->once())
            ->method('encode')
            ->will($this->throwException(new EncodingException));
        $this->contracts->registerContract($contract);
        $this->serializer->serialize('foo');
    }

    public function testSerializingArrayEncodesEachValueUsingContract(): void
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

    public function testSerializingValueSendsContractThroughInterceptors(): void
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
        $serializer = new JsonSerializer($this->contracts, [$encodingInterceptor]);
        $this->assertEquals('{"_id_":123,"_email_":"foo@bar.com"}', $serializer->serialize($user));
    }

    public function testSerializingValueJsonEncodesItsContract(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals('{"id":123,"email":"foo@bar.com"}', $this->serializer->serialize($user));
    }
}
