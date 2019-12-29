<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Console\Commands\CommandRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command registrant collection
 */
class CommandRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $commandRegistrants = new CommandRegistrantCollection();
        $singleRegistrant = new class() implements ICommandRegistrant
        {
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
}
