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
use Aphiria\Console\StatusCodes;

/**
 * Defines the default command bus
 */
final class CommandBus implements ICommandBus
{
    /** @var CommandHandlerBindingRegistry The command handler bindings */
    private $commandHandlerBindings;
    /** @var CommandInputFactory The factory to create command inputs with */
    private $commandInputFactory;

    /**
     * @param CommandHandlerBindingRegistry $commandHandlerBindings The command handler bindings
     * @param CommandInputFactory|null $commandInputFactory The factory to create command inputs with
     */
    public function __construct(
        CommandHandlerBindingRegistry $commandHandlerBindings,
        CommandInputFactory $commandInputFactory = null
    ) {
        $this->commandHandlerBindings = $commandHandlerBindings;
        $this->commandInputFactory = $commandInputFactory ?? new CommandInputFactory();
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request, IResponse $response): int
    {
        $binding = $this->commandHandlerBindings->getCommandHandlerBinding($request->commandName);
        $commandInput = $this->commandInputFactory->createCommandInput($binding->command, $request);

        if ($binding->commandHandler instanceof ICommandHandler) {
            $statusCode = $binding->commandHandler->handle($commandInput, $response);
        } else {
            // Assume a closure
            $statusCode = ($binding->commandHandler)($commandInput, $response);
        }

        return $statusCode ?? StatusCodes::OK;
    }
}
