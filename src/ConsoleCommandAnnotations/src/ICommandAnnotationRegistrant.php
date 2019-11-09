<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\ConsoleCommandAnnotations;

use Aphiria\Console\Commands\CommandRegistry;

/**
 * Defines the interface for command annotation registrants to implement
 */
interface ICommandAnnotationRegistrant
{
    /**
     * Registers command annotations to the command registry
     *
     * @param CommandRegistry $commands The registry to register to
     */
    public function registerCommands(CommandRegistry $commands): void;
}
