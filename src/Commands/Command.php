<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\Option;
use InvalidArgumentException;

/**
 * Defines a console command
 */
class Command
{
    /** @var string The name of the command */
    public $name;
    /** @var Argument[] The list of arguments */
    public $arguments;
    /** @var Option[] The list of options */
    public $options;
    /** @var string The description of the command */
    public $description;
    /** @var string|null The extra descriptive help text */
    public $helpText;

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
