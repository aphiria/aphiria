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
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Compilers\InputCompiler;
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

    /**
     * @param CommandRegistry $commands The commands
     * @param IInputCompiler $inputCompiler The input compiler
     */
    public function __construct(CommandRegistry $commands, IInputCompiler $inputCompiler = null)
    {
        // Set up our default commands
        $commands->registerManyCommands([
            new CommandBinding(new HelpCommand(), new HelpCommandHandler($commands)),
            new CommandBinding(new AboutCommand(), new AboutCommandHandler($commands))
        ]);
        $this->commands = $commands;
        $this->inputCompiler = $inputCompiler ?? new InputCompiler($this->commands);
    }

    /**
     * @inheritDoc
     */
    public function handle($rawInput, IOutput $output = null): int
    {
        try {
            $output = $output ?? new ConsoleOutput();
            // Default to the 'about' command if no command name is given
            $compiledInput = $this->inputCompiler->compile($rawInput === '' || $rawInput === [] ? 'about' : $rawInput);
            $binding = null;

            if (!$this->commands->tryGetBinding($compiledInput->commandName, $binding)) {
                throw new InvalidArgumentException("Command \"{$compiledInput->commandName}\" is not registered");
            }

            $statusCode = $binding->commandHandler->handle($compiledInput, $output);

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
