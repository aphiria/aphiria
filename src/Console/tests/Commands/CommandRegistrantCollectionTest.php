<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;

class CommandRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $commandRegistrants = new CommandRegistrantCollection();
        $singleRegistrant = new class () implements ICommandRegistrant {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                $this->wasInvoked = true;
            }
        };
        $commandRegistrants->add($singleRegistrant);
        $commands = new CommandRegistry();
        $commandRegistrants->registerCommands($commands);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }

    public function testCacheHitCopiesCachedConstraintsIntoParameterConstraints(): void
    {
        $cachedCommands = new CommandRegistry();
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $cachedCommands->registerCommand(new Command('foo'), $commandHandler::class);
        $cache = $this->createMock(ICommandRegistryCache::class);
        $cache->method('get')
            ->willReturn($cachedCommands);
        $collection = new CommandRegistrantCollection($cache);
        $paramCommands = new CommandRegistry();
        $collection->registerCommands($paramCommands);
        $this->assertEquals($cachedCommands, $paramCommands);
    }

    public function testCacheMissPopulatesCache(): void
    {
        $expectedCommands = new CommandRegistry();
        $cache = $this->createMock(ICommandRegistryCache::class);
        $cache->method('get')
            ->willReturn(null);
        $cache->method('set')
            ->with($expectedCommands);
        $collection = new CommandRegistrantCollection($cache);
        $collection->registerCommands($expectedCommands);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
