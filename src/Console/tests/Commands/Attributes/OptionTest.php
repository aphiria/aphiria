<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Option;
use Aphiria\Console\Input\OptionType;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testAllPropertiesAreSetInConstructor(): void
    {
        $option = new Option('opt', OptionType::REQUIRED_VALUE, 'o', 'description', 'foo');
        $this->assertSame('opt', $option->name);
        $this->assertSame(OptionType::REQUIRED_VALUE, $option->type);
        $this->assertSame('o', $option->shortName);
        $this->assertSame('description', $option->description);
        $this->assertSame('foo', $option->defaultValue);
    }
}
