<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands;

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\IBinderMetadataCollectionCache;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;

/**
 * Defines the console command handler that clears all the framework's caches
 */
class FlushFrameworkCachesCommandHandler implements ICommandHandler
{
    /** @var IBinderMetadataCollectionCache The binder metadata cache if one is set, or  null */
    private ?IBinderMetadataCollectionCache $binderMetadataCache;
    /** @var ICommandRegistryCache|null The console command cache if one is set, or null */
    private ?ICommandRegistryCache $commandCache;
    /** @var IRouteCache|null The route cache if one is set, or null */
    private ?IRouteCache $routeCache;
    /** @var ITrieCache|null The trie cache if one is set, or null */
    private ?ITrieCache $trieCache;
    /** @var IObjectConstraintsRegistryCache|null The object constraints cache if one is set, or null */
    private ?IObjectConstraintsRegistryCache $constraintCache;

    /**
     * @param IBinderMetadataCollectionCache|null $binderMetadataCache The binder metadata cache if one is set, or null
     * @param ICommandRegistryCache|null $commandCache The console command cache if one is set, or null
     * @param IRouteCache|null $routeCache The route cache if one is set, or null
     * @param ITrieCache|null $trieCache The trie cache if one is set, or null
     * @param IObjectConstraintsRegistryCache|null $constraintCache The object constraints cache if one is set, or null
     */
    public function __construct(
        ?IBinderMetadataCollectionCache $binderMetadataCache,
        ?ICommandRegistryCache $commandCache,
        ?IRouteCache $routeCache,
        ?ITrieCache $trieCache,
        ?IObjectConstraintsRegistryCache $constraintCache
    ) {
        $this->binderMetadataCache = $binderMetadataCache;
        $this->commandCache = $commandCache;
        $this->routeCache = $routeCache;
        $this->trieCache = $trieCache;
        $this->constraintCache = $constraintCache;
    }

    /**
     * @inheritdoc
     */
    public function handle(Input $input, IOutput $output)
    {
        if ($this->binderMetadataCache instanceof IBinderMetadataCollectionCache) {
            $this->binderMetadataCache->flush();
            $output->writeln('<info>Binder metadata cache flushed</info>');
        } else {
            $output->writeln('<info>Binder metadata cache not set - skipping</info>');
        }

        if ($this->commandCache instanceof ICommandRegistryCache) {
            $this->commandCache->flush();
            $output->writeln('<info>Console command cache flushed</info>');
        } else {
            $output->writeln('<info>Console command cache not set - skipping</info>');
        }

        if ($this->routeCache instanceof IRouteCache) {
            $this->routeCache->flush();
            $output->writeln('<info>Route cache flushed</info>');
        } else {
            $output->writeln('<info>Route cache not set - skipping</info>');
        }

        if ($this->trieCache instanceof ITrieCache) {
            $this->trieCache->flush();
            $output->writeln('<info>Trie cache flushed</info>');
        } else {
            $output->writeln('<info>Trie cache not set - skipping</info>');
        }

        if ($this->constraintCache instanceof IObjectConstraintsRegistryCache) {
            $this->constraintCache->flush();
            $output->writeln('<info>Constraints cache flushed</info>');
        } else {
            $output->writeln('<info>Constraints cache not set - skipping</info>');
        }

        $output->writeln('<success>Framework caches flushed</success>');
    }
}
