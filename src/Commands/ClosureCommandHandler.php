<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Commands;

use Aphiria\Console\Output\IOutput;
use Closure;

/**
 * Defines a command handler that executes a closure
 */
final class ClosureCommandHandler implements ICommandHandler
{
    /** @var Closure The closure that performs the actual logic of the command handler */
    private $closure;

    /**
     * @param Closure $closure The closure that performs the actual logic of the command handler
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritDoc
     */
    public function handle(CommandInput $commandInput, IOutput $output)
    {
        return ($this->closure)($commandInput, $output);
    }
}
