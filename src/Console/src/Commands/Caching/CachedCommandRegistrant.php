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

use Aphiria\Console\Commands\AggregateCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandRegistrant;

/**
 * Defines the command registry that initializes commands lazily
 */
final class CachedCommandRegistrant extends AggregateCommandRegistrant
{
    /** @var ICommandRegistryCache The commands cache to store compiled commands in */
    private ICommandRegistryCache $commandCache;

    /**
     * @inheritdoc
     * @param ICommandRegistryCache $commandCache The command cache
     */
    public function __construct(ICommandRegistryCache $commandCache, ICommandRegistrant $initialCommandRegistrant = null)
    {
        parent::__construct($initialCommandRegistrant);

        $this->commandCache = $commandCache;
    }

    /**
     * @inheritdoc
     */
    public function registerCommands(CommandRegistry $commands): void
    {
        if ($this->commandCache !== null && ($cachedCommands = $this->commandCache->get()) !== null) {
            $commands->registerManyCommands($cachedCommands->getAllCommandBindings());

            return;
        }

        parent::registerCommands($commands);

        // Save this to cache for next time
        if ($this->commandCache !== null) {
            $this->commandCache->set($commands);
        }
    }
}
