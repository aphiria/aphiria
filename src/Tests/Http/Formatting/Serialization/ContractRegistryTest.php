<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use DateTime;
use Opulence\Net\Http\Formatting\Serialization\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\DictionaryObjectContract;
use Opulence\Net\Http\Formatting\Serialization\Property;
use Opulence\Net\Http\Formatting\Serialization\ValueObjectContract;
use Opulence\Net\Tests\Http\Formatting\Serialization\Mocks\User;

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
        $expectedContract = new ValueObjectContract(
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
        $expectedContract = new ValueObjectContract(
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
        $expectedContract = new ValueObjectContract(
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

    public function testRegisteringDictionaryObjectContractCreatesAnInstanceOfOne(): void
    {
        $expectedContract = new DictionaryObjectContract(
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
        $this->contracts->registerDictionaryObjectContract(
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

    public function testRegisteringValueObjectContractCreatesAnInstanceOfOne(): void
    {
        $expectedContract = new ValueObjectContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
        $this->contracts->registerValueObjectContract(
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
