<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use InvalidArgumentException;

/**
 * Defines the interface for command buses to implement
 */
interface ICommandBus
{
    /**
     * Handles a console command
     *
     * @param Input|string|array $rawInput The raw input to parse
     * @param IOutput|null $output The output to write to, or null if using the default output
     * @return StatusCode|int The status code
     * @throws InvalidArgumentException Thrown if the raw input was invalid in any way
     */
    public function handle(Input|string|array $rawInput, IOutput $output = null): StatusCode|int;
}
