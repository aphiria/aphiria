<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

final class CommandInput
{
    /** @var array The mapping of argument names to values */
    public $arguments;
    /** @var array The mapping of option names to values */
    public $options;

    /**
     * @param array $arguments The mapping of argument names to values
     * @param array $options The option names to values
     */
    public function __construct(array $arguments, array $options)
    {
        $this->arguments = $arguments;
        $this->options = $options;
    }
}