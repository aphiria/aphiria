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
 * Mocks a simple command for use in testing
 */
class SimpleCommand extends Command
{
    /**
     * @param string $name The name of the command
     * @param string $description A brief description of the command
     * @param string $helpText The help text of the command
     */
    public function __construct($name, $description, $helpText = '')
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setHelpText($helpText);

        parent::__construct();
    }

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
