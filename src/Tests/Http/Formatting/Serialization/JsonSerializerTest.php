<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\ContractMapperRegistry;
use Opulence\Net\Http\Formatting\Serialization\IContractMapper;
use Opulence\Net\Http\Formatting\Serialization\ISerializationInterceptor;
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
    /** @var ContractMapperRegistry The contract mapper registry to use in tests */
    private $contractMapperRegistry;

    public function setUp(): void
    {
        $this->contractMapperRegistry = new ContractMapperRegistry();
        $this->serializer = new JsonSerializer($this->contractMapperRegistry);
    }

    public function testDeserializingInvalidJsonThrowsException(): void
    {
        $this->expectException(SerializationException::class);
        $this->serializer->deserialize('"', self::class);
    }

    public function testDeserializingTypeCreatesInstanceOfTypeFromContract(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IContractMapper $contractMapper */
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $contractMapper->expects($this->once())
            ->method('mapFromContract')
            ->with(['id' => 123, 'email' => 'foo@bar.com'])
            ->willReturn($user);
        $this->contractMapperRegistry->registerContractMapper($contractMapper);
        $this->assertSame($user, $this->serializer->deserialize('{"id":123,"email":"foo@bar.com"}', User::class));
    }

    public function testDeserializingValueSendContractThroughInterceptors(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IContractMapper $contractMapper */
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $contractMapper->expects($this->once())
            ->method('mapFromContract')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn($user);
        $this->contractMapperRegistry->registerContractMapper($contractMapper);
        /** @var ISerializationInterceptor $serializationInterceptor */
        $serializationInterceptor = $this->createMock(ISerializationInterceptor::class);
        $serializationInterceptor->expects($this->once())
            ->method('onDeserialization')
            ->with(['_id_' => 123, '_email_' => 'foo@bar.com'], User::class)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $serializer = new JsonSerializer($this->contractMapperRegistry, [$serializationInterceptor]);
        $this->assertSame($user, $serializer->deserialize('{"_id_":123,"_email_":"foo@bar.com"}', User::class));
    }

    public function testSerializingValueSendsContractThroughInterceptors(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IContractMapper $contractMapper */
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $contractMapper->expects($this->once())
            ->method('mapToContract')
            ->with($user)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $this->contractMapperRegistry->registerContractMapper($contractMapper);
        /** @var ISerializationInterceptor $serializationInterceptor */
        $serializationInterceptor = $this->createMock(ISerializationInterceptor::class);
        $serializationInterceptor->expects($this->once())
            ->method('onSerialization')
            ->with(['id' => 123, 'email' => 'foo@bar.com'], User::class)
            ->willReturn(['_id_' => 123, '_email_' => 'foo@bar.com']);
        $serializer = new JsonSerializer($this->contractMapperRegistry, [$serializationInterceptor]);
        $this->assertEquals('{"_id_":123,"_email_":"foo@bar.com"}', $serializer->serialize($user));
    }

    public function testSerializingValueJsonEncodesItsContract(): void
    {
        $user = new User(123, 'foo@bar.com');
        /** @var IContractMapper $contractMapper */
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $contractMapper->expects($this->once())
            ->method('mapToContract')
            ->with($user)
            ->willReturn(['id' => 123, 'email' => 'foo@bar.com']);
        $this->contractMapperRegistry->registerContractMapper($contractMapper);
        $this->assertEquals('{"id":123,"email":"foo@bar.com"}', $this->serializer->serialize($user));
    }
}
