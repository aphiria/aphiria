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

use Aphiria\Console\Commands\AggregateCommandRegistrant;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Console\Commands\CommandRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the aggregate command registrant
 */
class AggregateCommandRegistrantTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $aggregateRegistrant = new AggregateCommandRegistrant();
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
        $aggregateRegistrant->addCommandRegistrant($singleRegistrant);
        $commands = new CommandRegistry();
        $aggregateRegistrant->registerCommands($commands);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
