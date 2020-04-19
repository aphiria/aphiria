<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

/**
 * Defines the binding between a command and its handler
 */
final class CommandBinding
{
    /** @var Command The command */
    public Command $command;
    /** @var string The name of the command handler class */
    public string $commandHandlerClassName;

    /**
     * @param Command $command The command handler
     * @param string $commandHandlerClassName The name of the command handler class
     */
    public function __construct(Command $command, string $commandHandlerClassName)
    {
        $this->command = $command;
        $this->commandHandlerClassName = $commandHandlerClassName;
    }
}
