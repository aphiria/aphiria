<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
use Aphiria\Console\Input\Compilers\CommandNotFoundException;
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
                $this->writeExceptionMessage(new CommandNotFoundException($input->commandName), $output);

                return StatusCode::Error;
            }

            $commandHandler = $this->commandHandlerResolver->resolve($binding->commandHandlerClassName);
            $statusCode = $commandHandler->handle($input, $output);

            return $statusCode ?? StatusCode::Ok;
        } catch (Exception | Throwable $ex) {
            $this->writeExceptionMessage($ex, $output);

            return StatusCode::Fatal;
        }
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

    /**
     * Writes an exception message to the console output
     *
     * @param Exception|Throwable $ex The exception that was thrown
     * @param IOutput $output The output to write to
     */
    protected function writeExceptionMessage(Exception|Throwable $ex, IOutput $output): void
    {
        $output->writeln('<fatal>' . $ex::class . "</fatal>: <info>{$ex->getMessage()}</info>");
        $output->writeln('Stack trace:');
        $output->writeln($ex->getTraceAsString());
    }
}
