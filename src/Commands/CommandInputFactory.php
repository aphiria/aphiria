<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Input;
use RuntimeException;

/**
 * Defines a command input factory
 */
final class CommandInputFactory
{
    /**
     * Creates command input from a command and input
     *
     * @param Command $command The command whose input we're creating
     * @param Input $input The console input it's being created from
     * @return CommandInput The created command input
     * @throws RuntimeException Thrown if there was an error creating the command input
     */
    public function createCommandInput(Command $command, Input $input): CommandInput
    {
        $arguments = self::compileArguments($command, $input);
        $options = self::compileOptions($command, $input);

        return new CommandInput($arguments, $options);
    }

    /**
     * Compiles arguments in a command
     *
     * @param Command $command The command to compile
     * @param Input $input The user input
     * @return array The mapping of argument names to values
     * @throws RuntimeException Thrown if there are too many arguments
     */
    private static function compileArguments(Command $command, Input $input): array
    {
        // Need to make copies of these so that we aren't manipulating the actual arguments
        $inputArgumentValues = $input->argumentValues;
        $commandArguments = $command->arguments;
        $arguments = [];

        if (self::hasTooManyArguments($inputArgumentValues, $commandArguments)) {
            throw new RuntimeException('Too many arguments');
        }

        $hasSetArrayArgument = false;

        foreach ($commandArguments as $argument) {
            if (count($inputArgumentValues) === 0) {
                if (!$argument->isOptional()) {
                    throw new RuntimeException("Argument \"{$argument->name}\" does not have default value");
                }

                $arguments[$argument->name] = $argument->defaultValue;
            } else {
                if ($hasSetArrayArgument) {
                    throw new RuntimeException('Array argument must appear at end of list of arguments');
                }

                if ($argument->isArray()) {
                    // Add the rest of the values in the input to this argument
                    $restOfArgumentValues = [];

                    while (count($inputArgumentValues) > 0) {
                        $restOfArgumentValues[] = array_shift($inputArgumentValues);
                    }

                    $arguments[$argument->name] = $restOfArgumentValues;
                    $hasSetArrayArgument = true;
                } else {
                    $arguments[$argument->name] = array_shift($inputArgumentValues);
                }
            }
        }

        return $arguments;
    }

    /**
     * Compiles options in a command
     *
     * @param Command $command The command to compile
     * @param Input $input The user input
     * @return array The mapping of option names to values
     */
    private static function compileOptions(Command $command, Input $input): array
    {
        $options = [];

        foreach ($command->options as $option) {
            $shortNameIsSet = $option->shortName === null
                    ? false
                    : array_key_exists($option->shortName, $input->options);
            $longNameIsSet = array_key_exists($option->name, $input->options);

            // All options are optional (duh)
            if ($shortNameIsSet || $longNameIsSet) {
                $value = $longNameIsSet ? $input->options[$option->name] : $input->options[$option->shortName];

                if ($value !== null && !$option->valueIsPermitted()) {
                    throw new RuntimeException("Option \"{$option->name}\" does not permit a value");
                }

                if ($value === null && $option->valueIsRequired()) {
                    throw new RuntimeException("Option \"{$option->name}\" requires a value");
                }

                if ($value === null && $option->valueIsOptional()) {
                    $value = $option->defaultValue;
                }

                $options[$option->name] = $value;
            } elseif ($option->valueIsPermitted()) {
                // Set the value for the option to its default value, if values are permitted
                $options[$option->name] = $option->defaultValue;
            }
        }

        return $options;
    }

    /**
     * Gets whether or not there are too many argument values
     *
     * @param array $argumentValues The list of argument values
     * @param Argument[] $commandArguments The list of command arguments
     * @return bool True if there are too many arguments, otherwise false
     */
    private static function hasTooManyArguments(array $argumentValues, array $commandArguments): bool
    {
        if (count($argumentValues) > count($commandArguments)) {
            // Only when the last argument is an array do we allow more input arguments than command arguments
            if (count($commandArguments) === 0 || !end($commandArguments)->isArray()) {
                return true;
            }
        }

        return false;
    }
}
