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
final class CommandBinding
{
    /** @var Command The command */
    public $command;
    /** @var ICommandHandler The command handler that will handle the command */
    public $commandHandler;

    /**
     * @param Command $command The command handler
     * @param ICommandHandler|Closure $commandHandler The command handler/closure that will handle the command
     */
    public function __construct(Command $command, $commandHandler)
    {
        if ($commandHandler instanceof ICommandHandler) {
            $this->commandHandler = $commandHandler;
        } elseif ($commandHandler instanceof Closure) {
            $this->commandHandler = new ClosureCommandHandler($commandHandler);
        } else {
            throw new InvalidArgumentException(
                'Command handler must be instance of ' . Closure::class . ' or ' . ICommandHandler::class
            );
        }

        $this->command = $command;
    }
}
