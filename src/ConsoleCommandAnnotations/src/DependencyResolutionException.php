<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleCommandAnnotations;

use Exception;
use Throwable;

/**
 * Defines a dependency resolution exception
 */
final class DependencyResolutionException extends Exception
{
    /** @var string The name of the command handler that could not be resolved */
    private string $commandHandlerClassName;

    /**
     * @inheritdoc
     * @param string $commandHandlerClassName The name of the command handler that could not be resolved
     */
    public function __construct(string $commandHandlerClassName, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->commandHandlerClassName = $commandHandlerClassName;
    }

    /**
     * Gets the command handler that could not be resolved
     *
     * @return string The command handler that could not be resolved
     */
    public function getCommandHandlerClassName(): string
    {
        return $this->commandHandlerClassName;
    }
}
