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
use Closure;

/**
 * Defines the command registry factory that initializes commands lazily
 */
final class LazyCommandRegistryFactory implements ICommandRegistryFactory
{
    /** @var Closure[] The list of registrants that will actually register the commands */
    private array $commandRegistrants = [];
    /** @var ICommandRegistryCache|null The optional commands cache to store compiled commands in */
    private ?ICommandRegistryCache $commandCache;

    /**
     * @param Closure|null $commandRegistrant The initial registrant that will be used to register commands
     *      Note: Must take in an instance of CommandRegistry and be void
     * @param ICommandRegistryCache|null $commandCache The command cache, if we're using a cache, otherwise null
     */
    public function __construct(Closure $commandRegistrant = null, ICommandRegistryCache $commandCache = null)
    {
        if ($commandRegistrant !== null) {
            $this->commandRegistrants[] = $commandRegistrant;
        }

        $this->commandCache = $commandCache;
    }

    /**
     * Adds a command registrant
     *
     * @param Closure $commandRegistrant The factory to add
     *      Note: Must take in an instance of CommandRegistry and be void
     */
    public function addCommandRegistrant(Closure $commandRegistrant): void
    {
        $this->commandRegistrants[] = $commandRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function createCommands(): CommandRegistry
    {
        if ($this->commandCache !== null && ($commands = $this->commandCache->get()) !== null) {
            return $commands;
        }

        $commands = new CommandRegistry();

        foreach ($this->commandRegistrants as $commandRegistrant) {
            $commandRegistrant($commands);
        }

        // Save this to cache for next time
        if ($this->commandCache !== null) {
            $this->commandCache->set($commands);
        }

        return $commands;
    }
}
