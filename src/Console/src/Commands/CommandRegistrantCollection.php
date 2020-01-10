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

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;

/**
 * Defines the collection of command registrants that can be run in serial
 */
class CommandRegistrantCollection implements ICommandRegistrant
{
    /** @var ICommandRegistrant[] The list of registrants that will actually register the commands */
    protected array $commandRegistrants = [];
    /** @var ICommandRegistryCache|null The optional command registry cache */
    private ?ICommandRegistryCache $commandCache;

    /**
     * @param ICommandRegistryCache|null $commandCache The optional command cache
     */
    public function __construct(ICommandRegistryCache $commandCache = null)
    {
        $this->commandCache = $commandCache;
    }

    /**
     * Adds a command registrant
     *
     * @param ICommandRegistrant $commandRegistrant The registrant to add
     */
    public function add(ICommandRegistrant $commandRegistrant): void
    {
        $this->commandRegistrants[] = $commandRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        if ($this->commandCache !== null && ($cachedCommands = $this->commandCache->get()) !== null) {
            $commands->copy($cachedCommands);

            return;
        }

        foreach ($this->commandRegistrants as $commandRegistrant) {
            $commandRegistrant->registerCommands($commands);
        }

        if ($this->commandCache !== null) {
            $this->commandCache->set($commands);
        }
    }
}
