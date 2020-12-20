<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
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
    /** @var string The name of the command that was entered */
    private string $commandName;

    /**
     * @param string $commandName The name of the command that was entered
     * @param int $code The exception code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(string $commandName, int $code = 0, Throwable $previous = null)
    {
        $this->commandName = $commandName;

        parent::__construct("No command found with name \"{$this->commandName}\"", $code, $previous);
    }

    /**
     * Gets the command that was entered
     *
     * @return string The command name
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
