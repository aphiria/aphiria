<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\DataContractConverter;
use Opulence\Net\Http\Formatting\DataContractConverterRegistry;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;

/**
 * Tests the data contract converter
 */
class DataContractConverterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataContractConverter The data contract converter to use in tests */
    private $converter;
    /** @var DataContractConverterRegistry The registry of converters to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new DataContractConverterRegistry();
        $this->converter = new DataContractConverter($this->registry);
    }

    public function testConvertFromDataContractForModelWithoutConverterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->convertFromDataContract('foo', []);
    }

    public function testConvertingFromDataContractUsesRegisteredConverter(): void
    {
        $toDataContractConverter = function (User $user, DataContractConverter $converter) {
            return ['id' => $user->getId(), 'email' => $user->getEmail()];
        };
        $fromDataContractConverter = function (array $hash, DataContractConverter $converter) {
            return new User((int)$hash['id'], $hash['email']);
        };
        $this->registry->registerDataContractConverter(
            User::class,
            $toDataContractConverter,
            $fromDataContractConverter
        );
        $hash = ['id' => 123, 'email' => 'foo@bar.com'];
        $expectedUser = new User(123, 'foo@bar.com');
        $this->assertEquals($expectedUser, $this->converter->convertFromDataContract(User::class, $hash));
    }

    public function testConvertToDataContractForModelWithoutConverterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->convertToDataContract(new User(123, 'foo@bar.com'));
    }

    public function testConvertingToDataContractUsesRegisteredConverter(): void
    {
        $toDataContractConverter = function (User $user, DataContractConverter $converter) {
            return ['id' => $user->getId(), 'email' => $user->getEmail()];
        };
        $fromDataContractConverter = function (array $hash, DataContractConverter $converter) {
            return new User((int)$hash['id'], $hash['email']);
        };
        $this->registry->registerDataContractConverter(
            User::class,
            $toDataContractConverter,
            $fromDataContractConverter
        );
        $user = new User(123, 'foo@bar.com');
        $this->assertEquals(['id' => 123, 'email' => 'foo@bar.com'], $this->converter->convertToDataContract($user));
    }
}
