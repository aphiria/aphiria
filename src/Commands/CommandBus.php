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

/**
 * Defines the default command bus
 */
final class CommandBus implements ICommandBus
{
    /** @var CommandBindingRegistry The command bindings */
    private $commandBindings;
    /** @var CommandInputFactory The factory to create command inputs with */
    private $commandInputFactory;

    /**
     * @param CommandBindingRegistry $commandBindings The command bindings
     * @param CommandInputFactory|null $commandInputFactory The factory to create command inputs with
     */
    public function __construct(
        CommandBindingRegistry $commandBindings,
        CommandInputFactory $commandInputFactory = null
    ) {
        $this->commandBindings = $commandBindings;
        $this->commandInputFactory = $commandInputFactory ?? new CommandInputFactory();
    }

    /**
     * @inheritDoc
     */
    public function handle(Input $input, IOutput $output): int
    {
        $binding = $this->commandBindings->getCommandBinding($input->commandName);
        $commandInput = $this->commandInputFactory->createCommandInput($binding->command, $input);
        $statusCode = $binding->commandHandler->handle($commandInput, $output);

        return $statusCode ?? StatusCodes::OK;
    }
}
