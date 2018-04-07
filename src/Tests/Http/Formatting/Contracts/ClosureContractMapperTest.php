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

/**
 * Tests the closure contract mapper
 */
class ClosureContractMapperTest extends \PHPUnit\Framework\TestCase
{
    /** @var ClosureContractMapper The contract mapper to use in tests */
    private $contractMapper;

    public function setUp(): void
    {
        $this->contractMapper = new ClosureContractMapper(
            'sometype',
            function ($data) {
                $this->assertEquals('data', $data);

                return 'contract';
            },
            function ($contract) {
                $this->assertEquals('contract', $contract);

                return 'data';
            }
        );
    }

    public function testGettingTypeReturnsSameTypeSetInConstructor(): void
    {
        $this->assertEquals('sometype', $this->contractMapper->getType());
    }

    public function testMappingFromContractInvokesFromClosureWithData(): void
    {
        $this->assertEquals('data', $this->contractMapper->mapFromContract('contract'));
    }

    public function testMappingToContractInvokesFromClosureWithData(): void
    {
        $this->assertEquals('contract', $this->contractMapper->mapToContract('data'));
    }
}
