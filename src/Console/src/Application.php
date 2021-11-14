<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
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
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Compilers\CommandNotFoundException;
use Aphiria\Console\Input\Compilers\IInputCompiler;
use Aphiria\Console\Input\Compilers\InputCompiler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\IServiceResolver;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Defines a console application
 */
class Application implements ICommandBus
{
    /** @var IInputCompiler The input compiler to use */
    private readonly IInputCompiler $inputCompiler;

    /**
     * @param CommandRegistry $commands The commands
     * @param IServiceResolver $commandHandlerResolver The resolver of command handlers
     * @param IInputCompiler|null $inputCompiler The input compiler, or null if using the default one
     */
    public function __construct(
        private readonly CommandRegistry $commands,
        private readonly IServiceResolver $commandHandlerResolver,
        IInputCompiler $inputCompiler = null
    ) {
        $this->registerDefaultCommands();
        $this->inputCompiler = $inputCompiler ?? new InputCompiler($this->commands);
    }

    /**
     * @inheritdoc
     */
    public function handle(string|array $rawInput, IOutput $output = null): StatusCode|int
    {
        $output = $output ?? new ConsoleOutput();

        try {
            $compiledInput = $this->inputCompiler->compile($rawInput);
            /** @var CommandBinding|null $binding */
            $binding = null;

            if (!$this->commands->tryGetBinding($compiledInput->commandName, $binding)) {
                throw new InvalidArgumentException("Command \"{$compiledInput->commandName}\" is not registered");
            }

            /** @var ICommandHandler $commandHandler */
            $commandHandler = $this->commandHandlerResolver->resolve($binding->commandHandlerClassName);
            $statusCode = $commandHandler->handle($compiledInput, $output);

            return $statusCode ?? StatusCode::Ok;
        } catch (CommandNotFoundException $ex) {
            // If there was no entered command, treat it like invoking the about command
            if ($ex->commandName === '') {
                /** @var class-string<ICommandHandler>|null $aboutCommandHandlerClassName */
                $aboutCommandHandlerClassName = null;

                if (!$this->commands->tryGetHandlerClassName('about', $aboutCommandHandlerClassName)) {
                    $output->writeln('<fatal>About command not registered</fatal>');

                    return StatusCode::Fatal;
                }

                $output = $output ?? new ConsoleOutput();
                /** @var ICommandHandler $commandHandler */
                $commandHandler = $this->commandHandlerResolver->resolve($aboutCommandHandlerClassName);
                $commandHandler->handle(new Input('about', [], []), $output);

                return StatusCode::Ok;
            }

            $output->writeln("<error>{$this->formatExceptionMessage($ex)}</error>");

            return StatusCode::Error;
        } catch (InvalidArgumentException $ex) {
            $output->writeln("<error>{$this->formatExceptionMessage($ex)}</error>");

            return StatusCode::Error;
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
