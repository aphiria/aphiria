<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use InvalidArgumentException;

/**
 * Defines the interface for command buses to implement
 */
interface ICommandBus
{
    /**
     * Handles a command by name
     *
     * @param Input $input The input to handle
     * @param IOutput $output The output to write to
     * @return int The exit code
     * @throws InvalidArgumentException Thrown if the command did not exist or the input was invalid
     */
    public function handle(Input $input, IOutput $output): int;
}
