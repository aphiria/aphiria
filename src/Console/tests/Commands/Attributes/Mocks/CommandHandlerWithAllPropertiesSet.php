<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes\Mocks;

use Aphiria\Console\Commands\Attributes\Argument;
use Aphiria\Console\Commands\Attributes\Command;
use Aphiria\Console\Commands\Attributes\Option;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Output\IOutput;

/**
 * Mocks a command handler with all properties set
 */
#[
    Command('foo', 'command description', 'command help text'),
    Argument('arg1', ArgumentTypes::REQUIRED, 'arg1 description', 'arg1 value'),
    Option('opt1', OptionTypes::REQUIRED_VALUE, 'o', 'opt1 description', 'opt1 value')
]
final class CommandHandlerWithAllPropertiesSet implements ICommandHandler
{
    /**
     * @inheritdoc
     *
     * @return void
     */
    public function handle(Input $input, IOutput $output)
    {
    }
}
