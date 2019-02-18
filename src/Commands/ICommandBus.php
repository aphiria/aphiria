<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Requests\Request;
use Aphiria\Console\Responses\IResponse;
use InvalidArgumentException;

/**
 * Defines the interface for command buses to implement
 */
interface ICommandBus
{
    /**
     * Handles a command by name
     *
     * @param Request $request The request to handle
     * @param IResponse $response The response to write to
     * @return int The exit code
     * @throws InvalidArgumentException Thrown if the command did not exist or the request was invalid
     */
    public function handle(Request $request, IResponse $response): int;
}