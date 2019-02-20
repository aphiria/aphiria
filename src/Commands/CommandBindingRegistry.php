<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use InvalidArgumentException;

/**
 * Defines the command binding registry
 */
final class CommandBindingRegistry
{
    /** @var CommandBinding[] The mapping of command names to command bindings */
    private $commandBindings = [];

    /**
     * Gets all the command bindings
     *
     * @return CommandBinding[] The list of command bindings
     */
    public function getAllCommandBindings(): array
    {
        return array_values($this->commandBindings);
    }

    /**
     * Gets the command binding for a particular command name
     *
     * @param string $commandName The command whose binding we want
     * @return CommandBinding The binding
     * @throws InvalidArgumentException Thrown if no binding exists for the input command name
     */
    public function getCommandBinding(string $commandName): CommandBinding
    {
        $normalizedCommandName = self::normalizeCommandName($commandName);

        if (!isset($this->commandBindings[$normalizedCommandName])) {
            throw new InvalidArgumentException("No command with name $commandName is registered");
        }

        return $this->commandBindings[$normalizedCommandName];
    }

    /**
     * Registers a command binding
     *
     * @param CommandBinding $binding The binding to register
     */
    public function registerCommandBinding(CommandBinding $binding): void
    {
        $this->commandBindings[self::normalizeCommandName($binding->command->name)] = $binding;
    }

    /**
     * Registers many command bindings
     *
     * @param CommandBinding[] $bindings The bindings to register
     */
    public function registerManyCommandBindings(array $bindings): void
    {
        foreach ($bindings as $binding) {
            $this->registerCommandBinding($binding);
        }
    }

    /**
     * Normalizes a command name for use in the registry
     *
     * @param string $commandName The command name to normalize
     * @return string The normalized command name
     */
    private static function normalizeCommandName(string $commandName): string
    {
        return strtolower($commandName);
    }
}
