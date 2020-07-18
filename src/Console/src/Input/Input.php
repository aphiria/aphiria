<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

/**
 * Defines the input for a command
 */
final class Input
{
    /** @var string The name of the command that was invoked */
    public string $commandName;
    /** @var array The mapping of argument names to values */
    public array $arguments;
    /** @var array The mapping of option names to values */
    public array $options;

    /**
     * @param string $commandName The name of the command that was invoked
     * @param array $arguments The mapping of argument names to values
     * @param array $options The option names to values
     */
    public function __construct(string $commandName, array $arguments, array $options)
    {
        $this->commandName = $commandName;
        $this->arguments = $arguments;
        $this->options = $options;
    }
}
