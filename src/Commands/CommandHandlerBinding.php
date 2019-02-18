<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Closure;
use InvalidArgumentException;

/**
 * Defines the binding between a command and its handler
 */
final class CommandHandlerBinding
{
    /** @var Command The command */
    public $command;
    /** @var Closure|ICommandHandler The command handler/closure that will handle the command */
    public $commandHandler;

    /**
     * @param Command $command The command handler
     * @param Closure|ICommandHandler $commandHandler The command handler/closure that will handle the command
     */
    public function __construct(Command $command, $commandHandler)
    {
        if (!$commandHandler instanceof Closure && !$commandHandler instanceof ICommandHandler) {
            throw new InvalidArgumentException(
                'Command handler must be instance of ' . Closure::class . ' or ' . ICommandHandler::class
            );
        }

        $this->command = $command;
        $this->commandHandler = $commandHandler;
    }
}
