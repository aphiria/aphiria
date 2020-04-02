<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\Tokenizers\ArgvInputTokenizer;
use Aphiria\Console\Input\Tokenizers\ArrayListInputTokenizer;
use Aphiria\Console\Input\Tokenizers\IInputTokenizer;
use Aphiria\Console\Input\Tokenizers\StringInputTokenizer;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the input compiler
 */
final class InputCompiler implements IInputCompiler
{
    /** @var CommandRegistry The commands that are registered */
    private CommandRegistry $commands;
    /** @var IInputTokenizer The argv input tokenizer */
    private IInputTokenizer $argvTokenizer;
    /** @var IInputTokenizer The string input tokenizer */
    private IInputTokenizer $stringTokenizer;
    /** @var IInputTokenizer The array list input tokenizer */
    private IInputTokenizer $arrayListTokenizer;

    /**
     * @param CommandRegistry $commands The commands that are registered
     * @param IInputTokenizer|null $argvTokenizer The argv input tokenizer
     * @param IInputTokenizer|null $stringTokenizer The string input tokenizer
     * @param IInputTokenizer|null $arrayListTokenizer The array list input tokenizer
     */
    public function __construct(
        CommandRegistry $commands,
        IInputTokenizer $argvTokenizer = null,
        IInputTokenizer $stringTokenizer = null,
        IInputTokenizer $arrayListTokenizer = null
    ) {
        $this->commands = $commands;
        $this->argvTokenizer = $argvTokenizer ?? new ArgvInputTokenizer();
        $this->stringTokenizer = $stringTokenizer ?? new StringInputTokenizer();
        $this->arrayListTokenizer = $arrayListTokenizer ?? new ArrayListInputTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($rawInput): Input
    {
        $tokens = $this->selectTokenizer($rawInput)->tokenize($rawInput);

        if (\count($tokens) === 0) {
            throw new CommandNotFoundException('');
        }

        $commandName = '';
        $argumentValues = [];
        $options = [];
        self::parseTokens($tokens, $commandName, $argumentValues, $options);

        /** @var Command $command */
        if (!$this->commands->tryGetCommand($commandName, $command)) {
            throw new CommandNotFoundException($commandName);
        }

        return new Input(
            $command->name,
            self::compileArguments($command, $argumentValues),
            self::compileOptions($command, $options)
        );
    }

    /**
     * Gets the correct tokenizer for the input
     *
     * @param string|array $rawInput The input to use when figuring out which input tokenizer to use
     * @return IInputTokenizer The selected input tokenizer
     * @throws InvalidArgumentException Thrown if the input was neither a string nor an array
     */
    private function selectTokenizer($rawInput): IInputTokenizer
    {
        if (\is_string($rawInput)) {
            return $this->stringTokenizer;
        }

        if (\is_array($rawInput)) {
            if (isset($rawInput['name'])) {
                return $this->arrayListTokenizer;
            }

            return $this->argvTokenizer;
        }

        throw new InvalidArgumentException('Input must be either a string or an array');
    }

    /**
     * Adds an option to the list of options
     *
     * @param array $options The list of options we're adding to
     * @param string $name The name of the option to add
     * @param mixed $value The value to add
     */
    private static function addOption(array &$options, string $name, $value): void
    {
        if (isset($options[$name])) {
            // We now consider this option to have multiple values
            if (!\is_array($options[$name])) {
                $options[$name] = [$options[$name]];
            }

            $options[$name][] = $value;
        } else {
            $options[$name] = $value;
        }
    }

    /**
     * Compiles arguments in a command
     *
     * @param Command $command The command to compile
     * @param array $argumentValues The list of argument values
     * @return array The mapping of argument names to values
     * @throws RuntimeException Thrown if there are too many arguments
     */
    private static function compileArguments(Command $command, array $argumentValues): array
    {
        $arguments = [];

        if (self::hasTooManyArguments($argumentValues, $command->arguments)) {
            throw new RuntimeException('Too many arguments');
        }

        $hasSetArrayArgument = false;

        foreach ($command->arguments as $argument) {
            if (\count($argumentValues) === 0) {
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

                    while (\count($argumentValues) > 0) {
                        $restOfArgumentValues[] = array_shift($argumentValues);
                    }

                    $arguments[$argument->name] = $restOfArgumentValues;
                    $hasSetArrayArgument = true;
                } else {
                    $arguments[$argument->name] = array_shift($argumentValues);
                }
            }
        }

        return $arguments;
    }

    /**
     * Compiles options in a command
     *
     * @param Command $command The command to compile
     * @param array $rawOptions The list of raw options
     * @return array The mapping of option names to values
     */
    private static function compileOptions(Command $command, array $rawOptions): array
    {
        $options = [];

        foreach ($command->options as $option) {
            $shortNameIsSet = $option->shortName === null
                ? false
                : \array_key_exists($option->shortName, $rawOptions);
            $longNameIsSet = \array_key_exists($option->name, $rawOptions);

            // All options are optional (duh)
            if ($shortNameIsSet || $longNameIsSet) {
                $value = $longNameIsSet ? $rawOptions[$option->name] : $rawOptions[$option->shortName];

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
        if (\count($argumentValues) > \count($commandArguments)) {
            // Only when the last argument is an array do we allow more input arguments than command arguments
            if (\count($commandArguments) === 0 || !end($commandArguments)->isArray()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses an argument value
     *
     * @param string $token The token to parse
     * @return string The parsed argument
     */
    private static function parseArgument(string $token): string
    {
        return self::trimQuotes($token);
    }

    /**
     * Parses a long option token and returns an array of data
     *
     * @param string $token The token to parse
     * @param array $remainingTokens The list of remaining tokens
     * @return array The name of the option mapped to its value
     * @throws RuntimeException Thrown if the option could not be parsed
     */
    private static function parseLongOption(string $token, array &$remainingTokens): array
    {
        if (mb_strpos($token, '--') !== 0) {
            throw new RuntimeException("Invalid long option \"$token\"");
        }

        // Trim the "--"
        $option = mb_substr($token, 2);

        if (mb_strpos($option, '=') === false) {
            /**
             * The option is either of the form "--foo" or "--foo bar" or "--foo -b" or "--foo --bar"
             * So, we need to determine if the option has a value
             */
            $nextToken = array_shift($remainingTokens);

            // Check if the next token is also an option
            if (empty($nextToken) || mb_strpos($nextToken, '-') === 0) {
                // The option must have not had a value, so put the next token back
                array_unshift($remainingTokens, $nextToken);

                return [$option, null];
            }

            // Make it "--foo=bar"
            $option .= '=' . $nextToken;
        }

        [$name, $value] = explode('=', $option);
        $value = self::trimQuotes($value);

        return [$name, $value];
    }

    /**
     * Parses the tokens for the command name, arguments, and options
     *
     * @param array $tokens The tokens to parse
     * @param string $commandName The parsed command name
     * @param array $argumentValues The parsed input arguments
     * @param array $options The parsed input options
     * @throws InvalidArgumentException Thrown if the tokens are empty
     */
    private static function parseTokens(
        array $tokens,
        string &$commandName,
        array &$argumentValues,
        array &$options
    ): void {
        if (\count($tokens) === 0) {
            throw new InvalidArgumentException('Tokens cannot be empty');
        }

        $commandName = array_shift($tokens);

        while ($token = array_shift($tokens)) {
            if (mb_strpos($token, '--') === 0) {
                [$optionName, $optionValue] = self::parseLongOption($token, $tokens);
                self::addOption($options, $optionName, $optionValue);
            } elseif (mb_strpos($token, '-') === 0) {
                foreach (self::parseShortOption($token) as [$optionName, $optionValue]) {
                    self::addOption($options, $optionName, $optionValue);
                }
            } else {
                // We consider this to be an argument
                $argumentValues[] = self::parseArgument($token);
            }
        }
    }

    /**
     * Parses a short option token and returns an array of data
     *
     * @param string $token The token to parse
     * @return array The name of the option mapped to its value
     * @throws RuntimeException Thrown if the option could not be parsed
     */
    private static function parseShortOption(string $token): array
    {
        if (mb_strpos($token, '-') !== 0) {
            throw new RuntimeException("Invalid short option \"$token\"");
        }

        // Trim the "-"
        $token = mb_substr($token, 1);
        $options = [];

        // Each character in a short option is an option
        $tokens = preg_split('//u', $token, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($tokens as $singleToken) {
            $options[] = [$singleToken, null];
        }

        return $options;
    }

    /**
     * Trims the outer-most quotes from a token
     *
     * @param string $token Trims quotes off of a token
     * @return string The trimmed token
     */
    private static function trimQuotes(string $token): string
    {
        // Trim any quotes
        if (($firstValueChar = mb_substr($token, 0, 1)) === mb_substr($token, -1)) {
            if ($firstValueChar === "'") {
                $token = trim($token, "'");
            } elseif ($firstValueChar === '"') {
                $token = trim($token, '"');
            }
        }

        return $token;
    }
}
