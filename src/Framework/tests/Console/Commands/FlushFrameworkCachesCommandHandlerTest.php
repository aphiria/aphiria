<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
    private IOutput&MockObject $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
    }

    public function testBinderMetadataCacheIsFlushedIfSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Binder metadata cache flushed</info>', $correctOutputWritten);
        $binderMetadataCache = $this->createMock(IBinderMetadataCollectionCache::class);
        $binderMetadataCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler($binderMetadataCache, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testBinderMetadataCacheIsSkippedIfNotSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Binder metadata cache not set - skipping</info>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testConsoleCommandCacheIsFlushedIfSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Console command cache flushed</info>', $correctOutputWritten);
        $consoleCommandCache = $this->createMock(ICommandRegistryCache::class);
        $consoleCommandCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, $consoleCommandCache, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testConsoleCommandCacheIsSkippedIfNotSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Console command cache not set - skipping</info>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testConstraintsCacheIsFlushedIfSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Constraints cache flushed</info>', $correctOutputWritten);
        $constraintsCache = $this->createMock(IObjectConstraintsRegistryCache::class);
        $constraintsCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, $constraintsCache);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testConstraintsCacheIsSkippedIfNotSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Constraints cache not set - skipping</info>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testRouteCacheIsFlushedIfSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Route cache flushed</info>', $correctOutputWritten);
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, $routeCache, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testRouteCacheIsSkippedIfNotSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Route cache not set - skipping</info>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testSuccessMessageIsWrittenAtEnd(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<success>Framework caches flushed</success>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testTrieCacheIsFlushedIfSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Trie cache flushed</info>', $correctOutputWritten);
        $trieCache = $this->createMock(ITrieCache::class);
        $trieCache->expects($this->once())
            ->method('flush');
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, $trieCache, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    public function testTrieCacheIsSkippedIfNotSet(): void
    {
        $correctOutputWritten = false;
        $this->setUpMockOutput('<info>Trie cache not set - skipping</info>', $correctOutputWritten);
        $commandHandler = new FlushFrameworkCachesCommandHandler(null, null, null, null, null);
        $commandHandler->handle(new Input('framework:flushcaches', [], []), $this->output);
        $this->assertTrue($correctOutputWritten);
    }

    /**
     * Sets up the expected message to be written to output
     *
     * @param string $expectedMessage The expected message
     * @param bool $correctOutputWritten The "out" param for whether or not the correct output was written
     */
    private function setUpMockOutput(string $expectedMessage, bool &$correctOutputWritten): void
    {
        $correctOutputWritten = false;
        $this->output->method('writeln')
            ->with($this->callback(function (string $message) use ($expectedMessage, &$correctOutputWritten) {
                if ($message === $expectedMessage) {
                    $correctOutputWritten = true;
                }

                // We'll always return true, and check if the correct output was written later
                return true;
            }));
    }
}
