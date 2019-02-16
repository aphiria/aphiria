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
use Aphiria\Console\Requests\Argument;
use Aphiria\Console\Requests\ArgumentTypes;
use Aphiria\Console\Requests\Option;
use Aphiria\Console\Requests\OptionTypes;
use Aphiria\Console\Responses\IResponse;

/**
 * Mocks a command that does not call the parent constructor
 */
class CommandThatDoesNotCallParentConstructor extends Command
{
    public function __construct()
    {
        // Don't do anything
    }

    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        $this->setName('holiday');
        $this->setDescription('Wishes someone a happy holiday');
        $this->addArgument(new Argument(
            'holiday',
            ArgumentTypes::REQUIRED,
            'Holiday to wish someone'
        ));
        $this->addOption(new Option(
            'yell',
            'y',
            OptionTypes::OPTIONAL_VALUE,
            'Whether or not we yell',
            'yes'
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        $message = 'Happy ' . $this->getArgumentValue('holiday');

        if ($this->getOptionValue('yell') === 'yes') {
            $message .= '!';
        }

        $response->write($message);

        return null;
    }
}
