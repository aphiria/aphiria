<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Compilers;

use InvalidArgumentException;
use Throwable;

/**
 * Defines an exception that's thrown when no command was found with the input name
 */
final class CommandNotFoundException extends InvalidArgumentException
{
    /**
     * @param string $commandName The name of the command that was entered
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(public readonly string $commandName, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("No command found with name \"{$this->commandName}\"", $code, $previous);
    }
}
