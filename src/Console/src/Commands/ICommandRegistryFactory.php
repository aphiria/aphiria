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

/**
 * Defines the interfaces for command registry factories to implement
 */
interface ICommandRegistryFactory
{
    /**
     * Creates the command registry
     *
     * @return CommandRegistry The created commands
     */
    public function createCommands(): CommandRegistry;
}
