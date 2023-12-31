<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Drivers\IDriver;
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
     * Gets the CLI driver for the output
     *
     * @return IDriver The CLI driver
     */
    public function getDriver(): IDriver;

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
     * @param string|string[] $messages The message or messages to display
     * @throws RuntimeException Thrown if there was an issue writing the messages
     */
    public function write(string|array $messages): void;

    /**
     * Writes to output with a newline character at the end
     *
     * @param string|string[] $messages The message or messages to display
     * @throws RuntimeException Thrown if there was an issue writing the messages
     */
    public function writeln(string|array $messages): void;
}
