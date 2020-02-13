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

use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Closure;

/**
 * Defines a command handler that executes a closure
 */
final class ClosureCommandHandler implements ICommandHandler
{
    /** @var Closure The closure that performs the actual logic of the command handler */
    private Closure $closure;

    /**
     * @param Closure $closure The closure that performs the actual logic of the command handler
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        return ($this->closure)($input, $output);
    }
}
