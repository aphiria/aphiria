<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Caching;

use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandRegistrant;

/**
 * Defines the command registry that initializes commands lazily
 */
final class CachedCommandRegistrant implements ICommandRegistrant
{
    /** @var ICommandRegistryCache The commands cache to store compiled commands in */
    private ICommandRegistryCache $commandCache;
    /** @var CommandRegistrantCollection The list of command registrants to run on cache miss */
    private CommandRegistrantCollection $commandRegistrants;

    /**
     * @param ICommandRegistryCache $commandCache The command cache
     * @param CommandRegistrantCollection $commandRegistrants The list of command registrants to run on cache miss
     */
    public function __construct(ICommandRegistryCache $commandCache, CommandRegistrantCollection $commandRegistrants)
    {
        $this->commandCache = $commandCache;
        $this->commandRegistrants = $commandRegistrants;
    }

    /**
     * @inheritdoc
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        if (($cachedCommands = $this->commandCache->get()) !== null) {
            $commands->copy($cachedCommands);

            return;
        }

        $this->commandRegistrants->registerCommands($commands);

        // Save this to cache for next time
        $this->commandCache->set($commands);
    }
}
