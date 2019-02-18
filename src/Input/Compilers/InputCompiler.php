<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Input\Input;
use RuntimeException;

/**
 * Defines a base input compiler
 */
abstract class InputCompiler implements IInputCompiler
{
    /**
     * Compiles a list of tokens into an input
     *
     * @param array $tokens The tokens to compile
     * @return Input The compiled input
     * @throws RuntimeException Thrown if there is an invalid token
     */
    protected function compileTokens(array $tokens): Input
    {
        $commandName = null;
        $argumentValues = [];
        $options = [];
        $hasParsedCommandName = false;

        while ($token = array_shift($tokens)) {
            if (mb_strpos($token, '--') === 0) {
                $option = $this->parseLongOption($token, $tokens);
                self::addOption($options, $option[0], $option[1]);
            } elseif (mb_strpos($token, '-') === 0) {
                foreach ($this->parseShortOption($token) as $option) {
                    self::addOption($options, $option[0], $option[1]);
                }
            } elseif (!$hasParsedCommandName) {
                // We consider this to be the command name
                $commandName = $token;
                $hasParsedCommandName = true;
            } else {
                // We consider this to be an argument
                $argumentValues[] = $this->parseArgument($token);
            }
        }

        if ($commandName === null) {
            throw new RuntimeException('No command name specified');
        }

        return new Input($commandName, $argumentValues, $options);
    }

    /**
     * Parses an argument value
     *
     * @param string $token The token to parse
     * @return string The parsed argument
     */
    protected function parseArgument(string $token): string
    {
        return $this->trimQuotes($token);
    }

    /**
     * Parses a long option token and returns an array of data
     *
     * @param string $token The token to parse
     * @param array $remainingTokens The list of remaining tokens
     * @return array The name of the option mapped to its value
     * @throws RuntimeException Thrown if the option could not be parsed
     */
    protected function parseLongOption(string $token, array &$remainingTokens): array
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
        $value = $this->trimQuotes($value);

        return [$name, $value];
    }

    /**
     * Parses a short option token and returns an array of data
     *
     * @param string $token The token to parse
     * @return array The name of the option mapped to its value
     * @throws RuntimeException Thrown if the option could not be parsed
     */
    protected function parseShortOption(string $token): array
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
    protected function trimQuotes(string $token): string
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
            if (!is_array($options[$name])) {
                $options[$name] = [$options[$name]];
            }

            $options[$name][] = $value;
        } else {
            $options[$name] = $value;
        }
    }
}
