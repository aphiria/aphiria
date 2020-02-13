<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Closure;

/**
 * Defines a command registrant that uses a collection of closures to register commands
 */
final class ClosureCommandRegistrant implements ICommandRegistrant
{
    /** @var Closure[] The list of closures to execute */
    private array $closures;

    /**
     * @param Closure[] $closures The list of closures to execute
     */
    public function __construct(array $closures)
    {
        $this->closures = $closures;
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
