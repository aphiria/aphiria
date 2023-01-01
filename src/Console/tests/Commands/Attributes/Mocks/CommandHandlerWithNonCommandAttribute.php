<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes\Mocks;

use Aphiria\Console\Commands\Attributes\Command;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

/**
 * Mocks a command handler with a non-command attribute
 */
#[NonCommand, Command('foo')]
final class CommandHandlerWithNonCommandAttribute implements ICommandHandler
{
    /**
     * @inheritdoc
     *
     * @return void
     */
    public function handle(Input $input, IOutput $output)
    {
        // Don't do anything
    }
}
