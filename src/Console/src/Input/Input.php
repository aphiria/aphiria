<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input;

/**
 * Defines the input for a command
 */
final class Input
{
    /**
     * @param string $commandName The name of the command that was invoked
     * @param array<string, mixed> $arguments The mapping of argument names to values
     * @param array<string, mixed> $options The option names to values
     */
    public function __construct(
        public readonly string $commandName,
        public readonly array $arguments,
        public readonly array $options
    ) {
    }
}
