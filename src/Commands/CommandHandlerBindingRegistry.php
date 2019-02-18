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
 * Defines the command handler binding registry
 */
final class CommandHandlerBindingRegistry
{
    /** @var CommandHandlerBinding[] The mapping of command names to command handler bindings */
    private $commandHandlerBindings = [];

    /**
     * Gets all the command handler bindings
     *
     * @return CommandHandlerBinding[] The list of command handler bindings
     */
    public function getAllCommandHandlerBindings(): array
    {
        return array_values($this->commandHandlerBindings);
    }

    /**
     * Gets the command handler binding for a particular command name
     *
     * @param string $commandName The command whose binding we want
     * @return CommandHandlerBinding The binding
     * @throws InvalidArgumentException Thrown if no binding exists for the input command name
     */
    public function getCommandHandlerBinding(string $commandName): CommandHandlerBinding
    {
        $normalizedCommandName = self::normalizeCommandName($commandName);

        if (!isset($this->commandHandlerBindings[$normalizedCommandName])) {
            throw new InvalidArgumentException("No command with name $commandName is registered");
        }

        return $this->commandHandlerBindings[$normalizedCommandName];
    }

    /**
     * Registers a command handler binding
     *
     * @param CommandHandlerBinding $binding The binding to register
     */
    public function registerCommandHandlerBinding(CommandHandlerBinding $binding): void
    {
        $this->commandHandlerBindings[self::normalizeCommandName($binding->command->name)] = $binding;
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