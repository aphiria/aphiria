<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Annotations\Mocks\Finder\Subdirectory;

use Aphiria\Console\Commands\Annotations\Command;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;

/**
 * Defines a mock command handler
 * @Command("c")
 */
final class CommandHandlerC implements ICommandHandler
{
    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        return;
    }
}
