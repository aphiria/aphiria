<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests;

use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console option
 */
class OptionTest extends TestCase
{
    /** @var Option The option to use in tests */
    private $option;

    public function setUp(): void
    {
        $this->option = new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'Foo option', 'bar');
    }

    public function testCheckingIsValueArray(): void
    {
        $arrayOption = new Option('foo', 'f', OptionTypes::IS_ARRAY, 'Foo option');
        $this->assertTrue($arrayOption->valueIsArray());
    }

    public function testCheckingIsValueOptional(): void
    {
        $requiredOption = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo option', 'bar');
        $optionalArgument = new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'Foo option', 'bar');
        $this->assertFalse($requiredOption->valueIsOptional());
        $this->assertTrue($optionalArgument->valueIsOptional());
    }

    public function testCheckingIsValuePermitted(): void
    {
        $requiredOption = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo option', 'bar');
        $notPermittedOption = new Option('foo', 'f', OptionTypes::NO_VALUE, 'Foo option', 'bar');
        $this->assertTrue($requiredOption->valueIsPermitted());
        $this->assertFalse($notPermittedOption->valueIsPermitted());
    }

    public function testCheckingIsValueRequired(): void
    {
        $requiredOption = new Option('foo', 'f', OptionTypes::REQUIRED_VALUE, 'Foo option', 'bar');
        $optionalArgument = new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE, 'Foo option', 'bar');
        $this->assertTrue($requiredOption->valueIsRequired());
        $this->assertFalse($optionalArgument->valueIsRequired());
    }

    public function testGettingDefaultValue(): void
    {
        $this->assertEquals('bar', $this->option->defaultValue);
    }

    public function testGettingDescription(): void
    {
        $this->assertEquals('Foo option', $this->option->description);
    }

    public function testGettingName(): void
    {
        $this->assertEquals('foo', $this->option->name);
    }

    public function testGettingShortName(): void
    {
        $this->assertEquals('f', $this->option->shortName);
    }

    public function testNonAlphabeticShortName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', '-', OptionTypes::REQUIRED_VALUE, 'Foo option', 'bar');
    }

    public function testSettingTypeToOptionalAndNoValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE | OptionTypes::NO_VALUE, 'Foo argument');
    }

    public function testSettingTypeToOptionalAndRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', 'f', OptionTypes::OPTIONAL_VALUE | OptionTypes::REQUIRED_VALUE, 'Foo argument');
    }

    public function testSettingTypeToRequiredAndNoValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', 'f', OptionTypes::REQUIRED_VALUE | OptionTypes::NO_VALUE, 'Foo argument');
    }

    public function testTooLongShortName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', 'foo', OptionTypes::REQUIRED_VALUE, 'Foo option', 'bar');
    }
}
