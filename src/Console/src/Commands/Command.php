<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Option;

/**
 * Defines a console command
 */
class Command
{
    /**
     * @param string $name The name of the command
     * @param list<Argument> $arguments The list of arguments
     * @param list<Option> $options The list of options
     * @param string|null $description The description of the command
     * @param string|null $helpText the help text
     */
    public function __construct(
        public readonly string $name,
        public readonly array $arguments = [],
        public readonly array $options = [],
        public readonly ?string $description = null,
        public readonly ?string $helpText = null
    ) {
    }
}
