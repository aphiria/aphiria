<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Output\IOutput;
use InvalidArgumentException;

/**
 * Defines the interface for command buses to implement
 */
interface ICommandBus
{
    /**
     * Handles a console command
     *
     * @param string|array $rawInput The raw input to parse
     * @param IOutput $output The output to write to
     * @return int The status code
     * @throws InvalidArgumentException Thrown if the raw input was invalid in any way
     */
    public function handle($rawInput, IOutput $output = null): int;
}
