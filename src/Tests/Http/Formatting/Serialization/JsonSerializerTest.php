<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\IEncodingInterceptor;
use Opulence\Net\Http\Formatting\Serialization\JsonSerializer;
use Opulence\Net\Http\Formatting\Serialization\Property;
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
        $this->contracts->registerDictionaryObjectContract(
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

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"', self::class);
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
        $encodingInterceptor->expects($this->once())
            ->method('onDecoding')
            ->with(['id' => 321, 'email' => 'bar@foo.com'], User::class)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $serializer = new JsonSerializer($this->contracts, [$encodingInterceptor]);
        $this->assertEquals($user, $serializer->deserialize('{"id":321,"email":"bar@foo.com"}', User::class));
    }

    public function testSerializingValueSendsContractThroughInterceptors(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IEncodingInterceptor $encodingInterceptor */
        $encodingInterceptor = $this->createMock(IEncodingInterceptor::class);
        $encodingInterceptor->expects($this->once())
            ->method('onEncoding')
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
