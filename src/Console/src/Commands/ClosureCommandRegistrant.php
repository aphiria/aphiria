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

use Closure;

/**
 * Defines a command registrant that uses a collection of closures to register commands
 */
final class ClosureCommandRegistrant implements ICommandRegistrant
{
    /**
     * @param array<Closure(CommandRegistry): void> $closures The list of closures to execute
     */
    public function __construct(private array $closures)
    {
    }

    /**
     * @inheritdoc
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        foreach ($this->closures as $closure) {
            $closure($commands);
        }
    }
}
