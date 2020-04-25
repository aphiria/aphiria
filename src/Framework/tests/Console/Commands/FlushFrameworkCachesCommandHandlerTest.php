<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Binders\Metadata\Caching\IBinderMetadataCollectionCache;
use Aphiria\Framework\Console\Commands\FlushFrameworkCachesCommandHandler;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlushFrameworkCachesCommandHandlerTest extends TestCase
{
    /** @var IOutput|MockObject */
    private IOutput $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
    }

    public function testBinderMetadataCacheIsFlushedIfSet(): void
    {
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with('<info>Binder metadata cache flushed</info>');
        $binderMetadataCache = $this->createMock(IBinderMetadataCollectionCache::class);
        $binderMetadataCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler($binderMetadataCache, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testBinderMetadataCacheIsSkippedIfNotSet(): void
    {
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with('<info>Binder metadata cache not set - skipping</info>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testConsoleCommandCacheIsFlushedIfSet(): void
    {
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('<info>Console command cache flushed</info>');
        $consoleCommandCache = $this->createMock(ICommandRegistryCache::class);
        $consoleCommandCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, $consoleCommandCache, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testConsoleCommandCacheIsSkippedIfNotSet(): void
    {
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('<info>Console command cache not set - skipping</info>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testConstraintsCacheIsFlushedIfSet(): void
    {
        $this->output->expects($this->at(4))
            ->method('writeln')
            ->with('<info>Constraints cache flushed</info>');
        $constraintsCache = $this->createMock(IObjectConstraintsRegistryCache::class);
        $constraintsCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, $constraintsCache);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testConstraintsCacheIsSkippedIfNotSet(): void
    {
        $this->output->expects($this->at(4))
            ->method('writeln')
            ->with('<info>Constraints cache not set - skipping</info>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testRouteCacheIsFlushedIfSet(): void
    {
        $this->output->expects($this->at(2))
            ->method('writeln')
            ->with('<info>Route cache flushed</info>');
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, $routeCache, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testRouteCacheIsSkippedIfNotSet(): void
    {
        $this->output->expects($this->at(2))
            ->method('writeln')
            ->with('<info>Route cache not set - skipping</info>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testSuccessMessageIsWrittenAtEnd(): void
    {
        $this->output->expects($this->at(5))
            ->method('writeln')
            ->with('<success>Framework caches flushed</success>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testTrieCacheIsFlushedIfSet(): void
    {
        $this->output->expects($this->at(3))
            ->method('writeln')
            ->with('<info>Trie cache flushed</info>');
        $trieCache = $this->createMock(ITrieCache::class);
        $trieCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, $trieCache, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }

    public function testTrieCacheIsSkippedIfNotSet(): void
    {
        $this->output->expects($this->at(3))
            ->method('writeln')
            ->with('<info>Trie cache not set - skipping</info>');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
    }
}
