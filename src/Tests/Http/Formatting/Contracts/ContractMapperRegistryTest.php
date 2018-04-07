<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\ClosureContractMapper;
use Opulence\Net\Http\Formatting\Contracts\ContractMapperRegistry;
use Opulence\Net\Http\Formatting\Contracts\IContractMapper;
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

    public function testGettingContractMapperForTypeWithoutRegisteringOneThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->registry->getContractMapperForType('foo');
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
        $expectedContractMapper = $this->createMock(IContractMapper::class);
        $expectedContractMapper->expects($this->once())
            ->method('getType')
            ->willReturn('foo');
        $this->registry->registerContractMapper($expectedContractMapper);
        $this->assertSame($expectedContractMapper, $this->registry->getContractMapperForType('foo'));
    }
}
