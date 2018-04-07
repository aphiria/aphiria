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
}
