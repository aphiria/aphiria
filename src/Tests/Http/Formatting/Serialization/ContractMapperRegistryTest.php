<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\ClosureContractMapper;
use Opulence\Net\Http\Formatting\Serialization\ContractMapperRegistry;
use Opulence\Net\Http\Formatting\Serialization\IContractMapper;
use Opulence\Net\Tests\Http\Formatting\Serialization\Mocks\User;
use OutOfBoundsException;

/**
 * Tests the contract mapper registry
 */
class ContractMapperRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContractMapperRegistry The registry to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new ContractMapperRegistry();
    }

    public function testGettingcontractMapperForObjectValueReturnsOneForClassValue(): void
    {
        /** @var IContractMapper $expectedContractMapper */
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $this->registry->registerContractMapper($expectedContractMapper);
        $user = new User(123, 'foo@bar.com');
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForValue($user));
    }

    public function testGettingcontractMapperForScalarValueReturnsOneForScalarValue(): void
    {
        /** @var IContractMapper $expectedContractMapper */
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn('integer');
        $this->registry->registerContractMapper($expectedContractMapper);
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForValue(123));
    }

    public function testGettingcontractMapperForScalarValueReturnsOneForType(): void
    {
        /** @var IContractMapper $expectedContractMapper */
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn('int');
        $this->registry->registerContractMapper($expectedContractMapper);
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType('int'));
    }

    public function testGettingContractMapperForTypeNormalizesTypeName(): void
    {
        $typesAndNormalizedTypes = [
            ['boolean', 'bool'],
            ['double', 'float'],
            ['integer', 'int']
        ];

        // Test type to normalized type
        foreach ($typesAndNormalizedTypes as $typeAndNormalizedType) {
            [$type, $normalizedType] = $typeAndNormalizedType;
            $expectedContractMapper = $this->createMock(IContractMapper::class);
            $expectedContractMapper->expects($this->once())
                ->method('getType')
                ->willReturn($type);
            $this->registry->registerContractMapper($expectedContractMapper);
            $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType($normalizedType));
        }

        // Test normalized type to type
        foreach ($typesAndNormalizedTypes as $typeAndNormalizedType) {
            [$type, $normalizedType] = $typeAndNormalizedType;
            $expectedContractMapper = $this->createMock(IContractMapper::class);
            $expectedContractMapper->expects($this->once())
                ->method('getType')
                ->willReturn($normalizedType);
            $this->registry->registerContractMapper($expectedContractMapper);
            $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType($type));
        }
    }

    public function testGettingContractMapperForTypeWithoutRegisteringOneThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->registry->getContractMapperForType('foo');
    }

    public function testGettingcontractMapperForTypeReturnsOneForType(): void
    {
        /** @var IContractMapper $expectedContractMapper */
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $this->registry->registerContractMapper($expectedContractMapper);
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType(User::class));
    }

    public function testRegisteringClosureContractMapperCreatesInstanceOfClosureContractMapper(): void
    {
        $this->registry->registerClosureContractContractMapper(
            'foo',
            function () {
                return 'contract';
            },
            function () {
                return 'data';
            }
        );
        $contractMapper = $this->registry->getContractMapperForType('foo');
        $this->assertInstanceOf(ClosureContractMapper::class, $contractMapper);
        $this->assertEquals('foo', $contractMapper->getType());
    }

    public function testRegisteringContractMapperAndGettingItReturnTheSameInstance(): void
    {
        /** @var IContractMapper $expectedContractMapper */
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn('foo');
        $this->registry->registerContractMapper($expectedContractMapper);
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType('foo'));
    }
}
