<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
use RuntimeException;

/**
 * Defines the input compiler
 */
final class InputCompiler implements IInputCompiler
{
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
        private CommandRegistry $commands,
        IInputTokenizer $argvTokenizer = null,
        IInputTokenizer $stringTokenizer = null,
        IInputTokenizer $arrayListTokenizer = null
    ) {
        $this->argvTokenizer = $argvTokenizer ?? new ArgvInputTokenizer();
        $this->stringTokenizer = $stringTokenizer ?? new StringInputTokenizer();
        $this->arrayListTokenizer = $arrayListTokenizer ?? new ArrayListInputTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile(string|array $rawInput): Input
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
     * Adds an option to the list of options
     *
     * @param array<string, mixed> $options The list of options we're adding to
     * @param string $name The name of the option to add
     * @param mixed $value The value to add
     */
    private static function addOption(array &$options, string $name, mixed $value): void
    {
        if (isset($options[$name])) {
            // We now consider this option to have multiple values
            if (!\is_array($options[$name])) {
                $options[$name] = [$options[$name]];
            }

            /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
            $options[$name][] = $value;
        } else {
            /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
            $options[$name] = $value;
        }
    }

    /**
     * Compiles arguments in a command
     *
     * @param Command $command The command to compile
     * @param list<mixed> $argumentValues The list of argument values
     * @return array<string, mixed> The mapping of argument names to values
     * @throws RuntimeException Thrown if there are too many arguments
     */
    private static function compileArguments(Command $command, array $argumentValues): array
    {
        $arguments = [];

        if (self::hasTooManyArguments($argumentValues, $command->arguments)) {
            throw new RuntimeException('Too many arguments');
        }

        foreach ($command->arguments as $argument) {
            if (\count($argumentValues) === 0) {
                if (!$argument->isOptional()) {
                    throw new RuntimeException("Argument \"{$argument->name}\" does not have default value");
                }

                /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                $arguments[$argument->name] = $argument->defaultValue;
            } elseif ($argument->isArray()) {
                // Add the rest of the values in the input to this argument
                $restOfArgumentValues = [];

                while (\count($argumentValues) > 0) {
                    /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                    $restOfArgumentValues[] = \array_shift($argumentValues);
                }

                $arguments[$argument->name] = $restOfArgumentValues;
            } else {
                /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                $arguments[$argument->name] = \array_shift($argumentValues);
            }
        }

        return $arguments;
    }

    /**
     * Compiles options in a command
     *
     * @param Command $command The command to compile
     * @param array<string, mixed> $rawOptions The list of raw options
     * @return array<string, mixed> The mapping of option names to values
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
                /**
                 * @psalm-suppress PossiblyNullArrayOffset The short name will be set if the long one wasn't
                 * @psalm-suppress MixedAssignment We're purposely setting the value to a mixed type
                 */
                $value = $longNameIsSet ? $rawOptions[$option->name] : $rawOptions[$option->shortName];

                if ($value !== null && !$option->valueIsPermitted()) {
                    throw new RuntimeException("Option \"{$option->name}\" does not permit a value");
                }

                if ($value === null && $option->valueIsRequired()) {
                    throw new RuntimeException("Option \"{$option->name}\" requires a value");
                }

                if ($value === null && $option->valueIsOptional()) {
                    /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                    $value = $option->defaultValue;
                }

                /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                $options[$option->name] = $value;
            } elseif ($option->valueIsPermitted()) {
                // Set the value for the option to its default value, if values are permitted
                /** @psalm-suppress MixedAssignment We're purposely assigning to a mixed type */
                $options[$option->name] = $option->defaultValue;
            }
        }

        return $options;
    }

    /**
     * Gets whether or not there are too many argument values
     *
     * @param list<mixed> $argumentValues The list of argument values
     * @param list<Argument> $commandArguments The list of command arguments
     * @return bool True if there are too many arguments, otherwise false
     */
    private static function hasTooManyArguments(array $argumentValues, array $commandArguments): bool
    {
        if (\count($argumentValues) > \count($commandArguments)) {
            // Only when the last argument is an array do we allow more input arguments than command arguments
            if (\count($commandArguments) === 0 || !\end($commandArguments)->isArray()) {
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
     * @param list<mixed> $remainingTokens The list of remaining tokens
     * @return array{0: string, 1: string|null} The name of the option mapped to its value
     */
    private static function parseLongOption(string $token, array &$remainingTokens): array
    {
        // Trim the "--"
        $option = \mb_substr($token, 2);

        if (!\str_contains($option, '=')) {
            /**
             * The option is either of the form "--foo" or "--foo bar" or "--foo -b" or "--foo --bar"
             * So, we need to determine if the option has a value
             *
             * @psalm-suppress MixedAssignment We're purposely setting the token to a mixed type
             */
            $nextToken = \array_shift($remainingTokens);

            // Check if the next token is also an option
            if (empty($nextToken) || \str_starts_with((string)$nextToken, '-')) {
                // The option must have not had a value, so put the next token back
                \array_unshift($remainingTokens, $nextToken);

                /** @psalm-suppress ReferenceConstraintViolation The remaining tokens will continue to be a list */
                return [$option, null];
            }

            // Make it "--foo=bar"
            $option .= "=$nextToken";
        }

        [$name, $value] = \explode('=', $option);
        $value = self::trimQuotes($value);

        return [$name, $value];
    }

    /**
     * Parses the tokens for the command name, arguments, and options
     *
     * @param list<mixed> $tokens The tokens to parse
     * @param string $commandName The parsed command name
     * @param list<mixed> $argumentValues The parsed input arguments
     * @param array<string, mixed> $options The parsed input options
     */
    private static function parseTokens(
        array $tokens,
        string &$commandName,
        array &$argumentValues,
        array &$options
    ): void {
        // We're guaranteed that tokens is not empty from an upstream check
        $commandName = (string)\array_shift($tokens);

        /** @psalm-suppress MixedAssignment We're purposely setting the token to a mixed value */
        while ($token = \array_shift($tokens)) {
            if (\str_starts_with((string)$token, '--')) {
                [$optionName, $optionValue] = self::parseLongOption((string)$token, $tokens);
                self::addOption($options, $optionName, $optionValue);
            } elseif (\str_starts_with((string)$token, '-')) {
                foreach (self::parseShortOption((string)$token) as [$optionName, $optionValue]) {
                    self::addOption($options, $optionName, $optionValue);
                }
            } else {
                // We consider this to be an argument
                $argumentValues[] = self::parseArgument((string)$token);
            }
        }
    }

    /**
     * Parses a short option token and returns an array of data
     *
     * @param string $token The token to parse
     * @return array<array{0: string, 1: null}> The name of the option mapped to its value
     */
    private static function parseShortOption(string $token): array
    {
        // Trim the "-"
        $token = \mb_substr($token, 1);
        $options = [];

        // Each character in a short option is an option
        $tokens = \preg_split('//u', $token, -1, PREG_SPLIT_NO_EMPTY);

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
        if (($firstValueChar = \mb_substr($token, 0, 1)) === \mb_substr($token, -1)) {
            if ($firstValueChar === "'") {
                $token = \trim($token, "'");
            } elseif ($firstValueChar === '"') {
                $token = \trim($token, '"');
            }
        }

        return $token;
    }

    /**
     * Gets the correct tokenizer for the input
     *
     * @param string|array $rawInput The input to use when figuring out which input tokenizer to use
     * @return IInputTokenizer The selected input tokenizer
     */
    private function selectTokenizer(string|array $rawInput): IInputTokenizer
    {
        if (\is_string($rawInput)) {
            return $this->stringTokenizer;
        }

        if (isset($rawInput['name'])) {
            return $this->arrayListTokenizer;
        }

        return $this->argvTokenizer;
    }
}
