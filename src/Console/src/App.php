<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console;

use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\Defaults\AboutCommand;
use Aphiria\Console\Commands\Defaults\AboutCommandHandler;
use Aphiria\Console\Commands\Defaults\HelpCommand;
use Aphiria\Console\Commands\Defaults\HelpCommandHandler;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Compilers\InputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Defines a console application
 */
class App implements ICommandBus
{
    /** @var CommandRegistry The commands registered to the application */
    private CommandRegistry $commands;
    /** @var IInputCompiler The input compiler to use */
    private IInputCompiler $inputCompiler;

    /**
     * @param CommandRegistry $commands The commands
     * @param IInputCompiler $inputCompiler The input compiler
     */
    public function __construct(CommandRegistry $commands, IInputCompiler $inputCompiler = null)
    {
        $this->commands = $commands;
        $this->registerDefaultCommands();
        $this->inputCompiler = $inputCompiler ?? new InputCompiler($this->commands);
    }

    /**
     * @inheritdoc
     */
    public function handle($rawInput, IOutput $output = null): int
    {
        try {
            $output = $output ?? new ConsoleOutput();
            $compiledInput = $this->inputCompiler->compile($rawInput);
            /** @var CommandBinding|null $binding */
            $binding = null;

            if (!$this->commands->tryGetBinding($compiledInput->commandName, $binding)) {
                throw new InvalidArgumentException("Command \"{$compiledInput->commandName}\" is not registered");
            }

            $statusCode = $binding->resolveCommandHandler()->handle($compiledInput, $output);

            return $statusCode ?? StatusCodes::OK;
        } catch (CommandNotFoundException $ex) {
            // If there was no entered command, treat it like invoking the about command
            if ($ex->getCommandName() === '') {
                /** @var ICommandHandler $aboutCommandHandler */
                $aboutCommandHandler = null;

                if (!$this->commands->tryGetHandler('about', $aboutCommandHandler)) {
                    throw new RuntimeException('About command not registered');
                }

                $output = $output ?? new ConsoleOutput();
                $aboutCommandHandler->handle(new Input('about', [], []), $output);

                return StatusCodes::OK;
            }

            $output->writeln("<error>{$this->formatExceptionMessage($ex)}</error>");

            return StatusCodes::ERROR;
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>{$this->formatExceptionMessage($ex)}</error>");

            return StatusCodes::ERROR;
        } catch (Exception | Throwable $ex) {
            $output->writeln("<fatal>{$this->formatExceptionMessage($ex)}</fatal>");

            return StatusCodes::FATAL;
        }
    }

    /**
     * Formats an exception message to be more readable
     *
     * @param Exception|Throwable $ex The exception to format
     * @return string The formatted exception message
     */
    protected function formatExceptionMessage($ex): string
    {
        return $ex->getMessage() . \PHP_EOL . $ex->getTraceAsString();
    }

    /**
     * Registers any default commands to the application
     */
    protected function registerDefaultCommands(): void
    {
        $this->commands->registerManyCommands([
            new CommandBinding(
                new HelpCommand(),
                fn () => new HelpCommandHandler($this->commands)
            ),
            new CommandBinding(
                new AboutCommand(),
                fn () => new AboutCommandHandler($this->commands)
            )
        ]);
    }
}
