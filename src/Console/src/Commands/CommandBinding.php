<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

/**
 * Defines the binding between a command and its handler
 */
final readonly class CommandBinding
{
    /**
     * @param Command $command The command handler
     * @param class-string<ICommandHandler> $commandHandlerClassName The name of the command handler class
     */
    public function __construct(public Command $command, public string $commandHandlerClassName)
    {
    }
}
