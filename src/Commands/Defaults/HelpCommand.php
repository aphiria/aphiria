<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;

/**
 * Defines the help command
 */
final class HelpCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'help',
            [new Argument('command', ArgumentTypes::OPTIONAL, 'The command to get help with')],
            [],
            'Displays information about a command'
        );
    }
}