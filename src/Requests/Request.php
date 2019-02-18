<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests;

/**
 * Defines a basic console request
 */
final class Request
{
    /** @var string The name of the command entered */
    public $commandName;
    /** @var array The list of argument values in order */
    public $argumentValues = [];
    /** @var array The mapping of option names to values */
    public $options = [];

    /**
     * @param string $commandName The command name
     * @param array $argumentValues The list of argument values in order
     * @param array $options The mapping of option names to values
     */
    public function __construct(string $commandName, array $argumentValues, array $options)
    {
        $this->commandName = $commandName;
        $this->argumentValues = $argumentValues;
        $this->options = $options;
    }
}
