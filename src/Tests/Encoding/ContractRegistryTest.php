<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests;

use DateTime;
use Opulence\Serialization\Encoding\ContractRegistry;
use Opulence\Serialization\Encoding\ObjectContract;
use Opulence\Serialization\Encoding\Property;
use Opulence\Serialization\Encoding\StructContract;
use Opulence\Serialization\Tests\Mocks\User;
use OutOfBoundsException;

/**
 * Tests the contract registry
 */
class ContractRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContractRegistry The registry to use in tests */
    private $contracts;

    public function setUp(): void
    {
        $this->contracts = new ContractRegistry();
    }

    public function testGettingContractByObjectValueGetsContractRegisteredForItsType(): void
    {
        $expectedContract = new StructContract(
            DateTime::class,
            function ($value) {
                return DateTime::createFromFormat(DateTime::ISO8601, $value);
            },
            function (DateTime $value) {
                return $value->format(DateTime::ISO8601);
            }
        );
        $this->contracts->registerContract($expectedContract);
        $this->assertSame($expectedContract, $this->contracts->getContractForValue(new DateTime));
    }

    public function testGettingContractByScalarValueGetsContractRegisteredForItsType(): void
    {
        $expectedContract = new StructContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->contracts->registerContract($expectedContract);
        $this->assertSame($expectedContract, $this->contracts->getContractForValue(123));
    }

    public function testGettingContractByTypeGetsContractWithThatType(): void
    {
        $expectedContract = new StructContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->contracts->registerContract($expectedContract);
        $this->assertSame($expectedContract, $this->contracts->getContractForType('int'));
        $this->assertSame($expectedContract, $this->contracts->getContractForType('integer'));
    }

    public function testGettingContractForTypeWithoutContractThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->contracts->getContractForType('foo');
    }

    public function testGettingContractForValueWithoutContractThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->contracts->getContractForValue($this);
    }

    public function testRegisteringObjectContractCreatesAnInstanceOfOne(): void
    {
        $expectedContract = new ObjectContract(
            User::class,
            $this->contracts,
            function (array $hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->contracts->registerObjectContract(
            User::class,
            function (array $hash) {
                return new User($hash['id'], $hash['email']);
            },
            new Property('id', 'int', function (User $user) {
                return $user->getId();
            }),
            new Property('email', 'string', function (User $user) {
                return $user->getEmail();
            })
        );
        $this->assertEquals($expectedContract, $this->contracts->getContractForType(User::class));
    }

    public function testRegisteringStructContractCreatesAnInstanceOfOne(): void
    {
        $expectedContract = new StructContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->contracts->registerStructContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->assertEquals($expectedContract, $this->contracts->getContractForType('int'));
    }
}
