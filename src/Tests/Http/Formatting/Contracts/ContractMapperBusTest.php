<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\ArrayContract;
use Opulence\Net\Http\Formatting\Contracts\BoolContract;
use Opulence\Net\Http\Formatting\Contracts\ContractMapperBus;
use Opulence\Net\Http\Formatting\Contracts\ContractMapperRegistry;
use Opulence\Net\Http\Formatting\Contracts\DictionaryContract;
use Opulence\Net\Http\Formatting\Contracts\FloatContract;
use Opulence\Net\Http\Formatting\Contracts\IContractMapper;
use Opulence\Net\Http\Formatting\Contracts\IntContract;
use Opulence\Net\Http\Formatting\Contracts\StringContract;

/**
 * Tests the contract mapper bus
 */
class ContractMapperBusTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContractMapperBus The contract mapper bus to use in tests */
    private $bus;
    /** @var ContractMapperRegistry The contract mapper registry to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new ContractMapperRegistry();
        $this->bus = new ContractMapperBus($this->registry);
    }

    public function testMappingFromArrayContractUsesMapperFromRegistry(): void
    {
        $contract = new ArrayContract(['foo']);
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', ['bar']);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals(['bar'], $this->bus->mapFromArrayContract($contract, 'sometype'));
    }

    public function testMappingFromBoolContractUsesMapperFromRegistry(): void
    {
        $contract = new BoolContract(true);
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', true);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertTrue($this->bus->mapFromBoolContract($contract, 'sometype'));
    }

    public function testMappingFromDictionaryContractUsesMapperFromRegistry(): void
    {
        $contract = new DictionaryContract(['foo' => 'bar']);
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', ['foo' => 'bar']);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals(['foo' => 'bar'], $this->bus->mapFromDictionaryContract($contract, 'sometype'));
    }

    public function testMappingFromFloatContractUsesMapperFromRegistry(): void
    {
        $contract = new FloatContract(1.0);
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', 1.0);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals(1.0, $this->bus->mapFromFloatContract($contract, 'sometype'));
    }

    public function testMappingFromIntContractUsesMapperFromRegistry(): void
    {
        $contract = new IntContract(1);
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', 1);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals(1, $this->bus->mapFromIntContract($contract, 'sometype'));
    }

    public function testMappingFromStringContractUsesMapperFromRegistry(): void
    {
        $contract = new StringContract('foo');
        $contractMapper = $this->createContractMapperThatMapsFromContract($contract, 'sometype', 'foo');
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals('foo', $this->bus->mapFromStringContract($contract, 'sometype'));
    }

    public function testMappingToArrayContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new ArrayContract(['foo']);
        $contractMapper = $this->createContractMapperThatMapsToContract(['foo'], $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToArrayContract(['foo']));
    }

    public function testMappingToBoolContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new BoolContract(true);
        $contractMapper = $this->createContractMapperThatMapsToContract(true, $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToBoolContract(true));
    }

    public function testMappingToDictionaryContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new DictionaryContract(['foo' => 'bar']);
        $contractMapper = $this->createContractMapperThatMapsToContract(['foo' => 'bar'], $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToDictionaryContract(['foo' => 'bar']));
    }

    public function testMappingToFloatContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new FloatContract(1.0);
        $contractMapper = $this->createContractMapperThatMapsToContract(1.0, $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToFloatContract(1.0));
    }

    public function testMappingToIntContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new IntContract(1);
        $contractMapper = $this->createContractMapperThatMapsToContract(1, $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToIntContract(1));
    }

    public function testMappingToStringContractUsesMapperFromRegistry(): void
    {
        $expectedContract = new StringContract('foo');
        $contractMapper = $this->createContractMapperThatMapsToContract('foo', $expectedContract);
        $this->registry->registerContractMapper($contractMapper);
        $this->assertEquals($expectedContract, $this->bus->mapToStringContract('foo'));
    }

    /**
     * Creates a mock contract mapper that maps from a contract
     *
     * @param ArrayContract|BoolContract|DictionaryContract|FloatContract|IntContract|StringContract $expectedContract The expected contract
     * @param string $expectedType The expected type to map to
     * @param mixed $expectedReturnValue The expected return value
     * @return IContractMapper|\PHPUnit_Framework_MockObject_MockObject The mock contract mapper
     */
    private function createContractMapperThatMapsFromContract(
        $expectedContract,
        string $expectedType,
        $expectedReturnValue
    ): IContractMapper {
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn($expectedType);
        $contractMapper->expects($this->once())
            ->method('mapFromContract')
            ->with($expectedContract)
            ->willReturn($expectedReturnValue);

        return $contractMapper;
    }

    /**
     * Creates a mock contract mapper that maps to a contract
     *
     * @param mixed $expectedValue The expected value to map to a contract
     * @param ArrayContract|BoolContract|DictionaryContract|FloatContract|IntContract|StringContract $expectedContract The expected contract value
     * @return IContractMapper|\PHPUnit_Framework_MockObject_MockObject The mock contract mapper
     */
    private function createContractMapperThatMapsToContract($expectedValue, $expectedContract): IContractMapper
    {
        $contractMapper = $this->createMock(IContractMapper::class);
        $contractMapper->expects($this->once())
            ->method('getType')
            ->willReturn(is_object($expectedValue) ? get_class($expectedValue) : gettype($expectedValue));
        $contractMapper->expects($this->once())
            ->method('mapToContract')
            ->with($expectedValue)
            ->willReturn($expectedContract);

        return $contractMapper;
    }
}
