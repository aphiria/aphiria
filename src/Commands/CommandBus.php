<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use InvalidArgumentException;

/**
 * Defines the default command bus
 */
final class CommandBus implements ICommandBus
{
    /** @var CommandRegistry The command registry */
    private $commands;
    /** @var CommandInputFactory The factory to create command inputs with */
    private $commandInputFactory;

    /**
     * @param CommandRegistry $commands The command registry
     * @param CommandInputFactory|null $commandInputFactory The factory to create command inputs with
     */
    public function __construct(
        CommandRegistry $commands,
        CommandInputFactory $commandInputFactory = null
    ) {
        $this->commands = $commands;
        $this->commandInputFactory = $commandInputFactory ?? new CommandInputFactory();
    }

    /**
     * @inheritDoc
     */
    public function handle(Input $input, IOutput $output): int
    {
        $binding = null;

        if (!$this->commands->tryGetBinding($input->commandName, $binding)) {
            throw new InvalidArgumentException("Command \"{$input->commandName}\" is not registered");
        }

        $commandInput = $this->commandInputFactory->createCommandInput($binding->command, $input);
        $statusCode = $binding->commandHandler->handle($commandInput, $output);

        return $statusCode ?? StatusCodes::OK;
    }
}
