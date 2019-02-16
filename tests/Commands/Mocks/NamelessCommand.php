<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands\Mocks;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Responses\IResponse;

/**
 * Defines a command without a name
 */
class NamelessCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        $response->write('foo');

        return null;
    }
}
