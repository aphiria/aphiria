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
use Aphiria\Console\Commands\CommandBus;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Input\Compilers\ArgvInputCompiler;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Defines the console kernel
 */
final class Kernel
{
    /** @var ICommandBus The command bus that can handle commands */
    private $commandBus;
    /** @var IInputCompiler The input compiler to use */
    private $inputCompiler;

    /**
     * @param CommandRegistry $commands The commands
     * @param IInputCompiler|null $inputCompiler The input compiler to use
     */
    public function __construct(CommandRegistry $commands, IInputCompiler $inputCompiler = null)
    {
        // Set up our default commands
        $commands->registerManyCommands([
            new CommandBinding(new HelpCommand(), new HelpCommandHandler($commands)),
            new CommandBinding(new AboutCommand(), new AboutCommandHandler($commands))
        ]);
        $this->commandBus = new CommandBus($commands);
        $this->inputCompiler = $inputCompiler ?? new ArgvInputCompiler();
    }

    /**
     * Handles a console command
     *
     * @param mixed $rawInput The raw input to parse
     * @param IOutput $output The output to write to
     * @return int The status code
     */
    public function handle($rawInput, IOutput $output = null): int
    {
        $output = $output ?? new ConsoleOutput();

        try {
            $input = $this->inputCompiler->compile($rawInput);

            // Handle no command name being invoked as the same thing as invoking the about command
            if ($input->commandName === '') {
                $aboutInput = new Input('about', $input->argumentValues, $input->options);

                return $this->commandBus->handle($aboutInput, $output);
            }

            return $this->commandBus->handle($input, $output);
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");

            return StatusCodes::ERROR;
        } catch (Exception | Throwable $ex) {
            $output->writeln("<fatal>{$ex->getMessage()}</fatal>");

            return StatusCodes::FATAL;
        }
    }
}
