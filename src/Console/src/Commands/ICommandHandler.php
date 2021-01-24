<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

/**
 * Defines the interface for command handlers to implement
 */
interface ICommandHandler
{
    /**
     * Handles a command
     *
     * @param Input $input The input to handle
     * @param IOutput $output The output to write to
     * @return int|void The status code if there was one, or void, which assumes an status code of 0
     */
    public function handle(Input $input, IOutput $output);
}
