<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\Argument;
use Aphiria\Console\Input\ArgumentTypes;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    public function testAllPropertiesAreSetInConstructor(): void
    {
        $argument = new Argument('arg', ArgumentTypes::REQUIRED, 'description', 'foo');
        $this->assertSame('arg', $argument->name);
        $this->assertSame(ArgumentTypes::REQUIRED, $argument->type);
        $this->assertSame('description', $argument->description);
        $this->assertSame('foo', $argument->defaultValue);
    }
}
