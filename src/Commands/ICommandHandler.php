<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Responses\IResponse;

/**
 * Defines the interface for command handlers to implement
 */
interface ICommandHandler
{
    /**
     * Handles a command
     *
     * @param CommandInput $commandInput The input to handle
     * @param IResponse $response The response to write to
     * @return int|void The status code if there was one, or void, which assumes an status code of 0
     */
    public function handle(CommandInput $commandInput, IResponse $response);
}
