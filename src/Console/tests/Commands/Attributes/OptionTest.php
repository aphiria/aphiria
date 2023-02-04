<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Option;
use Aphiria\Console\Input\OptionType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testAllPropertiesAreSetInConstructor(): void
    {
        $option = new Option('opt', OptionType::RequiredValue, 'o', 'description', 'foo');
        $this->assertSame('opt', $option->name);
        $this->assertSame([OptionType::RequiredValue], $option->type);
        $this->assertSame('o', $option->shortName);
        $this->assertSame('description', $option->description);
        $this->assertSame('foo', $option->defaultValue);
    }

    /**
     * @param list<OptionType> $expectedType The expected type
     * @param list<OptionType>|OptionType $paramType The type passed into the option constructor
     */
    #[TestWith([[OptionType::RequiredValue], OptionType::RequiredValue])]
    #[TestWith([[OptionType::RequiredValue], [OptionType::RequiredValue]])]
    public function testTypeIsAlwaysArray(array $expectedType, array|OptionType $paramType): void
    {
        $option = new Option('opt', $paramType);
        $this->assertSame($expectedType, $option->type);
    }
}
