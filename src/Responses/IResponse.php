<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses;

use RuntimeException;

/**
 * Defines the interface for console responses to implement
 */
interface IResponse
{
    /**
     * Clears the response from view
     */
    public function clear(): void;

    /**
     * Sets whether or not messages should be styled
     *
     * @param bool $isStyled Whether or not messages should be styled
     */
    public function setStyled(bool $isStyled): void;

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
