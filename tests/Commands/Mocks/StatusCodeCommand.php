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
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use Aphiria\Console\Responses\IResponse;
use Aphiria\Console\StatusCodes;

/**
 * Mocks a command that returns a different status code depending on the options
 */
class StatusCodeCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        $this->setName('statuscode');
        $this->setDescription('Returns a status code based on the options');
        $this->addOption(new Option(
            'code',
            'c',
            OptionTypes::REQUIRED_VALUE,
            'The status code to return',
            StatusCodes::OK
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        return (int)$this->getOptionValue('code');
    }
}
