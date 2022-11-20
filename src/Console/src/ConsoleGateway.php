<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console;

use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\IServiceResolver;
use Exception;
use Throwable;

/**
 * Defines the gateway into a console application
 */
class ConsoleGateway implements ICommandHandler
{
    /**
     * @param CommandRegistry $commands The commands the application accepts
     * @param IServiceResolver $commandHandlerResolver The resolver of command handlers
     */
    public function __construct(
        private readonly CommandRegistry $commands,
        private readonly IServiceResolver $commandHandlerResolver
    ) {
        $this->registerDefaultCommands();
    }

    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        try {
            /** @var CommandBinding|null $binding */
            $binding = null;

            if (!$this->commands->tryGetBinding($input->commandName, $binding)) {
                // If there was no entered command, treat it like invoking the about command
                if ($input->commandName === '') {
                    /** @var class-string<ICommandHandler>|null $aboutCommandHandlerClassName */
                    $aboutCommandHandlerClassName = null;

                    if (!$this->commands->tryGetHandlerClassName('about', $aboutCommandHandlerClassName)) {
                        $output->writeln('<fatal>About command not registered</fatal>');

                        return StatusCode::Fatal;
                    }

                    $commandHandler = $this->commandHandlerResolver->resolve($aboutCommandHandlerClassName);
                    $commandHandler->handle(new Input('about', [], []), $output);

                    return StatusCode::Ok;
                }

                $output->writeln("<error>No command found with name \"{$input->commandName}\"</error>");

                return StatusCode::Error;
            }

            $commandHandler = $this->commandHandlerResolver->resolve($binding->commandHandlerClassName);
            $statusCode = $commandHandler->handle($input, $output);

            return $statusCode ?? StatusCode::Ok;
        } catch (Exception | Throwable $ex) {
            $output->writeln("<fatal>{$this->formatExceptionMessage($ex)}</fatal>");

            return StatusCode::Fatal;
        }
    }

    /**
     * Formats an exception message to be more readable
     *
     * @param Exception|Throwable $ex The exception to format
     * @return string The formatted exception message
     */
    protected function formatExceptionMessage(Exception|Throwable $ex): string
    {
        return $ex->getMessage() . \PHP_EOL . $ex->getTraceAsString();
    }

    /**
     * Registers any default commands to the application
     */
    protected function registerDefaultCommands(): void
    {
        $this->commands->registerManyCommands([
            new CommandBinding(new HelpCommand(), HelpCommandHandler::class),
            new CommandBinding(new AboutCommand(), AboutCommandHandler::class)
        ]);
    }
}
