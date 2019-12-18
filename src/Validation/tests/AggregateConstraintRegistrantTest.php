<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\AggregateConstraintRegistrant;
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\IConstraintRegistrant;
use PHPUnit\Framework\TestCase;

/**
 * Tests the aggregate constraint registrant
 */
class AggregateConstraintRegistrantTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $aggregateRegistrant = new AggregateConstraintRegistrant();
        $singleRegistrant = new class() implements IConstraintRegistrant
        {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerConstraints(ConstraintRegistry $constraints): void
            {
                $this->wasInvoked = true;
            }
        };
        $aggregateRegistrant->addConstraintRegistrant($singleRegistrant);
        $constraints = new ConstraintRegistry();
        $aggregateRegistrant->registerConstraints($constraints);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
