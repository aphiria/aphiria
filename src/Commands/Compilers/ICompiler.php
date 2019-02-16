<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands\Compilers;

use Aphiria\Console\Commands\ICommand;
use Aphiria\Console\Requests\IRequest;
use RuntimeException;

/**
 * Defines the interface for command compilers to implement
 */
interface ICompiler
{
    /**
     * Compiles a command using request data
     *
     * @param ICommand $command The command to compile
     * @param IRequest $request The request from the user
     * @return ICommand The compiled command
     * @throws RuntimeException Thrown if there was an error compiling the command
     */
    public function compile(ICommand $command, IRequest $request): ICommand;
}
