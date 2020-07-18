<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

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
