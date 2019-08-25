<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /** @var string The name of the command */
    public string $name;
    /** @var Argument[] The list of arguments */
    public array $arguments;
    /** @var Option[] The list of options */
    public array $options;
    /** @var string The description of the command */
    public string $description;
    /** @var string|null The extra descriptive help text */
    public ?string $helpText;

    /**
     * @param string $name The name of the command
     * @param Argument[] $arguments The list of arguments
     * @param Option[] $options The list of options
     * @param string $description The description of the command
     * @param string $helpText the help text
     */
    public function __construct(
        string $name,
        array $arguments,
        array $options,
        string $description,
        string $helpText = null
    ) {
        if (empty($name)) {
            throw new InvalidArgumentException('Command name cannot be empty');
        }

        $this->name = $name;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->description = $description;
        $this->helpText = $helpText;
    }
}
