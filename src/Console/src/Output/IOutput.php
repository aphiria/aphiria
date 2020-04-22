<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Drivers\ITerminalDriver;
use RuntimeException;

/**
 * Defines the interface for console outputs to implement
 */
interface IOutput
{
    /**
     * Clears the output from view
     */
    public function clear(): void;

    /**
     * Gets the terminal driver for the output
     *
     * @return ITerminalDriver The terminal driver
     */
    public function getTerminalDriver(): ITerminalDriver;

    /**
     * Sets whether or not messages should be styled
     *
     * @param bool $includeStyles Whether or not messages should be styled
     */
    public function includeStyles(bool $includeStyles): void;

    /**
     * Reads a line from input
     *
     * @return string The line that was input
     * @throws RuntimeException Thrown if there was an issue reading the input
     */
    public function readLine(): string;

    /**
     * Writes to output
     *
     * @param string|array $messages The message or messages to display
     * @throws RuntimeException Thrown if there was an issue writing the messages
     */
    public function write($messages): void;

    /**
     * Writes to output with a newline character at the end
     *
     * @param string|array $messages The message or messages to display
     * @throws RuntimeException Thrown if there was an issue writing the messages
     */
    public function writeln($messages): void;
}
