<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Closure;
use InvalidArgumentException;

/**
 * Defines the registry of commands
 */
final class CommandRegistry
{
    /** @var CommandBinding[] The mapping o command names to their bindings */
    private array $bindings = [];

    /**
     * Performs a deep clone of objects (used in some of our tests)
     */
    public function __clone()
    {
        foreach ($this->bindings as $name => $binding) {
            $this->bindings[$name] = clone $binding;
        }
    }

    /**
     * Copies a command registry into this one
     *
     * @param CommandRegistry $commands The commands to copy
     */
    public function copy(CommandRegistry $commands): void
    {
        $this->bindings = $commands->bindings;
    }

    /**
     * Gets the list of all command bindings
     *
     * @return CommandBinding[] The list of command bindings
     */
    public function getAllCommandBindings(): array
    {
        return array_values($this->bindings);
    }

    /**
     * Gets a list of all commands
     *
     * @return Command[] The list of commands
     */
    public function getAllCommands(): array
    {
        $commands = [];

        foreach ($this->bindings as $binding) {
            $commands[] = $binding->command;
        }

        return $commands;
    }

    /**
     * Registers a command
     *
     * @param Command $command The command to register
     * @param Closure $commandHandlerFactory The factory that will create the command handler
     *      Note: This must be parameterless
     * @throws InvalidArgumentException Thrown if the command handler is not a Closure nor a command handler
     */
    public function registerCommand(Command $command, Closure $commandHandlerFactory): void
    {
        $this->bindings[self::normalizeCommandName($command->name)] = new CommandBinding($command, $commandHandlerFactory);
    }

    /**
     * Registers many commands
     *
     * @param CommandBinding[] $bindings The command bindings to register
     */
    public function registerManyCommands(array $bindings): void
    {
        foreach ($bindings as $binding) {
            $this->bindings[self::normalizeCommandName($binding->command->name)] = $binding;
        }
    }

    /**
     * Tries to find the command binding for a particular command name
     *
     * @param string $commandName The command name to search for
     * @param CommandBinding|null $binding The command binding, if there was one
     * @return bool True if there was a binding for the command name, otherwise false
     */
    public function tryGetBinding(string $commandName, ?CommandBinding &$binding): bool
    {
        $normalizedCommandName = self::normalizeCommandName($commandName);

        if (!isset($this->bindings[$normalizedCommandName])) {
            return false;
        }

        $binding = $this->bindings[$normalizedCommandName];

        return true;
    }

    /**
     * Tries to find the command with a particular command name
     *
     * @param string $commandName The command name to search for
     * @param Command|null $command The command, if there was one
     * @return bool True if there was a command with the input name, otherwise false
     */
    public function tryGetCommand(string $commandName, ?Command &$command): bool
    {
        /** @var CommandBinding|null $binding */
        $binding = null;

        if (!$this->tryGetBinding($commandName, $binding)) {
            return false;
        }

        $command = $binding->command;

        return true;
    }

    /**
     * Tries to find the command handler for a particular command
     *
     * @param Command|string $command Either the command name or the instance of the command
     * @param ICommandHandler|null $commandHandler The command handler, if there was one
     * @return bool True if there was a handler for the command, otherwise false
     * @throws InvalidArgumentException Thrown if the command was not a string nor a Command
     */
    public function tryGetHandler($command, ?ICommandHandler &$commandHandler): bool
    {
        if (is_string($command)) {
            $commandName = $command;
        } elseif ($command instanceof Command) {
            $commandName = $command->name;
        } else {
            throw new InvalidArgumentException('Command must be either a string or an instance of ' . Command::class);
        }

        /** @var CommandBinding|null $binding */
        $binding = null;

        if (!$this->tryGetBinding($commandName, $binding)) {
            return false;
        }

        $commandHandler = $binding->resolveCommandHandler();

        return true;
    }

    /**
     * Normalizes a command name for storage
     *
     * @param string $commandName The command name to normalize
     * @return string The normalized command name
     */
    private static function normalizeCommandName(string $commandName): string
    {
        return strtolower($commandName);
    }
}
