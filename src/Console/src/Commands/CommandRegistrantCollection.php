<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands;

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;

/**
 * Defines the collection of command registrants that can be run in serial
 */
class CommandRegistrantCollection implements ICommandRegistrant
{
    /** @var list<ICommandRegistrant> The list of registrants that will actually register the commands */
    protected array $commandRegistrants = [];

    /**
     * @param ICommandRegistryCache|null $commandCache The optional command cache
     */
    public function __construct(private ?ICommandRegistryCache $commandCache = null)
    {
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
        if (($cachedCommands = $this->commandCache?->get()) !== null) {
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
