<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input;

use Aphiria\Console\Input\Option;
use Aphiria\Console\Input\OptionType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    private Option $option;

    protected function setUp(): void
    {
        $this->option = new Option('foo', OptionType::OptionalValue, 'f', 'Foo option', 'bar');
    }

    public function testCheckingIsValueArray(): void
    {
        $arrayOption = new Option('foo', OptionType::IsArray, 'f', 'Foo option');
        $this->assertTrue($arrayOption->valueIsArray());
    }

    public function testCheckingIsValueOptional(): void
    {
        $requiredOption = new Option('foo', OptionType::RequiredValue, 'f', 'Foo option', 'bar');
        $optionalArgument = new Option('foo', OptionType::OptionalValue, 'f', 'Foo option', 'bar');
        $this->assertFalse($requiredOption->valueIsOptional());
        $this->assertTrue($optionalArgument->valueIsOptional());
    }

    public function testCheckingIsValuePermitted(): void
    {
        $requiredOption = new Option('foo', OptionType::RequiredValue, 'f', 'Foo option', 'bar');
        $notPermittedOption = new Option('foo', OptionType::NoValue, 'f', 'Foo option', 'bar');
        $this->assertTrue($requiredOption->valueIsPermitted());
        $this->assertFalse($notPermittedOption->valueIsPermitted());
    }

    public function testCheckingIsValueRequired(): void
    {
        $requiredOption = new Option('foo', OptionType::RequiredValue, 'f', 'Foo option', 'bar');
        $optionalArgument = new Option('foo', OptionType::OptionalValue, 'f', 'Foo option', 'bar');
        $this->assertTrue($requiredOption->valueIsRequired());
        $this->assertFalse($optionalArgument->valueIsRequired());
    }

    public function testEmptyOptionNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option name cannot be empty');
        new Option('', OptionType::NoValue);
    }

    public function testGettingDefaultValue(): void
    {
        $this->assertSame('bar', $this->option->defaultValue);
    }

    public function testGettingDescription(): void
    {
        $this->assertSame('Foo option', $this->option->description);
    }

    public function testGettingName(): void
    {
        $this->assertSame('foo', $this->option->name);
    }

    public function testGettingShortName(): void
    {
        $this->assertSame('f', $this->option->shortName);
    }

    public function testNonAlphabeticShortName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', OptionType::RequiredValue, '-', 'Foo option', 'bar');
    }

    public function testSettingTypeToOptionalAndNoValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', [OptionType::OptionalValue, OptionType::NoValue], 'f', 'Foo argument');
    }

    public function testSettingTypeToOptionalAndRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', [OptionType::OptionalValue, OptionType::RequiredValue], 'f', 'Foo argument');
    }

    public function testSettingTypeToRequiredAndNoValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', [OptionType::RequiredValue, OptionType::NoValue], 'f', 'Foo argument');
    }

    public function testTooLongShortName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Option('foo', OptionType::RequiredValue, 'foo', 'Foo option', 'bar');
    }

    /**
     * @param list<OptionType> $expectedType The expected type
     * @param list<OptionType>|OptionType $paramType The type passed into the constructor
     */
    #[TestWith([[OptionType::RequiredValue], OptionType::RequiredValue])]
    #[TestWith([[OptionType::RequiredValue], [OptionType::RequiredValue]])]
    public function testTypeIsAlwaysArray(array $expectedType, array|OptionType $paramType): void
    {
        $option = new Option('opt', $paramType);
        $this->assertSame($expectedType, $option->type);
    }
}
