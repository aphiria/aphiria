<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use InvalidArgumentException;

/**
 * Defines the registry of commands
 */
final class CommandRegistry
{
    /** @var CommandBinding[] The mapping o command names to their bindings */
    private array $bindings = [];

    /**
     * Copies a command registry into this one
     *
     * @param CommandRegistry $commands The commands to copy
     * @internal
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
     * @param string $commandHandlerClassName The name of the command handler class
     * @throws InvalidArgumentException Thrown if the command handler is not a Closure nor a command handler
     */
    public function registerCommand(Command $command, string $commandHandlerClassName): void
    {
        $this->bindings[self::normalizeCommandName($command->name)] = new CommandBinding($command, $commandHandlerClassName);
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
     * @param-out CommandBinding $binding
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
     * @param-out Command $command
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
     * @param string|null $commandHandlerClassName The command handler class name, if there was one
     * @param-out string $commandHandlerClassName
     * @return bool True if there was a handler for the command, otherwise false
     */
    public function tryGetHandlerClassName(Command|string $command, ?string &$commandHandlerClassName): bool
    {
        if (\is_string($command)) {
            $commandName = $command;
        } else {
            $commandName = $command->name;
        }

        /** @var CommandBinding|null $binding */
        $binding = null;

        if (!$this->tryGetBinding($commandName, $binding)) {
            return false;
        }

        $commandHandlerClassName = $binding->commandHandlerClassName;

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
