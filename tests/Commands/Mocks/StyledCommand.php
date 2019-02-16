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
 * Mocks a command with styled output
 */
class StyledCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        $this->setName('stylish');
        $this->setDescription('Shows an output with style');
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        $response->write("<b>I've got style</b>");

        return null;
    }
}
