<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console;

use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandInputFactory;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Input\Compilers\ArgvInputCompiler;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Defines the console kernel
 */
final class Kernel implements ICommandBus
{
    /** @var CommandRegistry The commands registered to the kernel */
    private $commands;
    /** @var IInputCompiler The input compiler to use */
    private $inputCompiler;
    /** @var CommandInputFactory The factory to create command inputs with */
    private $commandInputFactory;

    /**
     * @param CommandRegistry $commands The commands
     * @param IInputCompiler|null $inputCompiler The input compiler to use
     * @param CommandInputFactory $commandInputFactory The factory that can create command inputs
     */
    public function __construct(
        CommandRegistry $commands,
        IInputCompiler $inputCompiler = null,
        CommandInputFactory $commandInputFactory = null
    ) {
        // Set up our default commands
        $commands->registerManyCommands([
            new CommandBinding(new HelpCommand(), new HelpCommandHandler($commands)),
            new CommandBinding(new AboutCommand(), new AboutCommandHandler($commands))
        ]);
        $this->commands = $commands;
        $this->inputCompiler = $inputCompiler ?? new ArgvInputCompiler();
        $this->commandInputFactory = $commandInputFactory ?? new CommandInputFactory();
    }

    /**
     * @inheritDoc
     */
    public function handle($rawInput, IOutput $output = null): int
    {
        $output = $output ?? new ConsoleOutput();

        try {
            // Default to the 'about' command if no command name is given
            $input = $this->inputCompiler->compile($rawInput === '' || $rawInput === [] ? 'about' : $rawInput);
            $binding = null;

            if (!$this->commands->tryGetBinding($input->commandName, $binding)) {
                throw new InvalidArgumentException("Command \"{$input->commandName}\" is not registered");
            }

            $commandInput = $this->commandInputFactory->createCommandInput($binding->command, $input);
            $statusCode = $binding->commandHandler->handle($commandInput, $output);

            return $statusCode ?? StatusCodes::OK;
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");

            return StatusCodes::ERROR;
        } catch (Exception | Throwable $ex) {
            $output->writeln("<fatal>{$ex->getMessage()}</fatal>");

            return StatusCodes::FATAL;
        }
    }
}
