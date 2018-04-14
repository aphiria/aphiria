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
use Opulence\Net\Http\Formatting\DataContractConverterRegistry;

/**
 * Tests the data contract converter registry
 */
class DataContractConverterRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataContractConverterRegistry The data contract converter registry to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new DataContractConverterRegistry;
    }

    public function testGettingFromDataContractConverterForTypeWithoutConverterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getFromDataContractConverter('foo');
    }

    public function testGettingToDataContractConverterForTypeWithoutConverterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->getToDataContractConverter('foo');
    }

    public function testGettingConvertersReturnsConvertersRegisteredForType(): void
    {
        $toDataContractConverter = function () {
        };
        $fromDataContractConverter = function () {
        };
        $this->registry->registerDataContractConverter('foo', $toDataContractConverter, $fromDataContractConverter);
        $this->assertSame($toDataContractConverter, $this->registry->getToDataContractConverter('foo'));
        $this->assertSame($fromDataContractConverter, $this->registry->getFromDataContractConverter('foo'));
    }
}
