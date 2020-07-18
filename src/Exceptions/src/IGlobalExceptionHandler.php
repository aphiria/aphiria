<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions;

use ErrorException;
use Throwable;

/**
 * Defines the interface for global exception handlers to implement
 */
interface IGlobalExceptionHandler
{
    /**
     * Handles an error
     *
     * @param int $level The level of the error
     * @param string $message The message
     * @param string $file The file the error occurred in
     * @param int $line The line number the error occurred at
     * @param array $context The symbol table
     * @throws ErrorException Thrown if the error was reportable based on its level
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = []): void;

    /**
     * Handles an exception
     *
     * @param Throwable $ex The exception to handle
     */
    public function handleException(Throwable $ex): void;

    /**
     * Handles a PHP shutdown
     */
    public function handleShutdown(): void;

    /**
     * Registers the exception and error handlers with PHP
     */
    public function registerWithPhp(): void;
}
