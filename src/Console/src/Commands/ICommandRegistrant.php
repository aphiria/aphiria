<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

/**
 * Defines the interfaces for command registrants to implement
 */
interface ICommandRegistrant
{
    /**
     * Registers console commands to the registry
     *
     * @param CommandRegistry $commands The commands to register to
     */
    public function registerCommands(CommandRegistry $commands): void;
}
