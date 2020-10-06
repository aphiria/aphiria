<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Option;
use Aphiria\Console\Input\OptionTypes;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testAllPropertiesAreSetInConstructor(): void
    {
        $option = new Option('opt', OptionTypes::REQUIRED_VALUE, 'o', 'description', 'foo');
        $this->assertSame('opt', $option->name);
        $this->assertSame(OptionTypes::REQUIRED_VALUE, $option->type);
        $this->assertSame('o', $option->shortName);
        $this->assertSame('description', $option->description);
        $this->assertSame('foo', $option->defaultValue);
    }
}
