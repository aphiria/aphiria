<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Defaults;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\ArgumentType;

/**
 * Defines the help command
 */
final class HelpCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'help',
            [new Argument('command', ArgumentType::Optional, 'The command to get help with')],
            [],
            'Displays information about a command'
        );
    }
}
