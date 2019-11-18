<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

/**
 * Defines the command registrant that aggregates other command registrants
 */
class AggregateCommandRegistrant implements ICommandRegistrant
{
    /** @var ICommandRegistrant[] The list of registrants that will actually register the commands */
    protected array $commandRegistrants = [];

    /**
     * @param ICommandRegistrant|null $initialCommandRegistrant The initial registrant to register, or null
     */
    public function __construct(ICommandRegistrant $initialCommandRegistrant = null)
    {
        if ($initialCommandRegistrant !== null) {
            $this->commandRegistrants[] = $initialCommandRegistrant;
        }
    }

    /**
     * Adds a command registrant
     *
     * @param ICommandRegistrant $commandRegistrant The registrant to add
     */
    public function addCommandRegistrant(ICommandRegistrant $commandRegistrant): void
    {
        $this->commandRegistrants[] = $commandRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        foreach ($this->commandRegistrants as $commandRegistrant) {
            $commandRegistrant->registerCommands($commands);
        }
    }
}
