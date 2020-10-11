<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\Console\Input\Argument;
use Aphiria\Console\Input\Option;
use InvalidArgumentException;

/**
 * Defines a console command
 */
class Command
{
    /**
     * @param string $name The name of the command
     * @param Argument[] $arguments The list of arguments
     * @param Option[] $options The list of options
     * @param string|null $description The description of the command
     * @param string|null $helpText the help text
     */
    public function __construct(
        public string $name,
        public array $arguments = [],
        public array $options = [],
        public ?string $description = null,
        public ?string $helpText = null
    ) {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Command name cannot be empty');
        }
    }
}
