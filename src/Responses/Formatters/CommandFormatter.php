<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Formatters;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\Option;

/**
 * Formats a command into a text representation
 */
class CommandFormatter
{
    /**
     * Gets the command as text
     *
     * @param Command $command The command to convert
     * @return string The command as text
     */
    public function format(Command $command): string
    {
        $text = $command->name . ' ';

        // Output the options
        foreach ($command->options as $option) {
            $text .= $this->formatOption($option) . ' ';
        }

        /** @var Argument[] $requiredArguments */
        $requiredArguments = [];
        /** @var Argument[] $optionalArguments */
        $optionalArguments = [];
        /** @var Argument $arrayArgument */
        $arrayArgument = null;

        // Categorize each argument
        foreach ($command->arguments as $argument) {
            if ($argument->isRequired() && !$argument->isArray()) {
                $requiredArguments[] = $argument;
            } elseif ($argument->isOptional() && !$argument->isArray()) {
                $optionalArguments[] = $argument;
            }

            if ($argument->isArray()) {
                $arrayArgument = $argument;
            }
        }

        // Output the required arguments
        foreach ($requiredArguments as $argument) {
            $text .= $argument->name . ' ';
        }

        // Output the optional arguments
        foreach ($optionalArguments as $argument) {
            $text .= "[{$argument->name}] ";
        }

        // Output the array argument
        if ($arrayArgument !== null) {
            $text .= $this->formatArrayArgument($arrayArgument);
        }

        return trim($text);
    }

    /**
     * Formats an array argument
     *
     * @param Argument $argument The argument to format
     * @return string The formatted array argument
     */
    private function formatArrayArgument(Argument $argument): string
    {
        $arrayArgumentTextOne = $argument->name . '1';
        $arrayArgumentTextN = $argument->name . 'N';

        if ($argument->isOptional()) {
            $arrayArgumentTextOne = "[$arrayArgumentTextOne]";
            $arrayArgumentTextN = "[$arrayArgumentTextN]";
        }

        return "$arrayArgumentTextOne...$arrayArgumentTextN";
    }

    /**
     * Formats an option
     *
     * @param Option $option The option to format
     * @return string The formatted option
     */
    private function formatOption(Option $option): string
    {
        $text = "[--{$option->name}";

        if ($option->valueIsOptional()) {
            $text .= '=' . $option->defaultValue;
        }

        if ($option->shortName !== null) {
            $text .= "|-{$option->shortName}";
        }

        $text .= ']';

        return $text;
    }
}
