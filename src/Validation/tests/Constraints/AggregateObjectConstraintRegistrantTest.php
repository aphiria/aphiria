<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\AggregateObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\IObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the aggregate constraint registrant
 */
class AggregateObjectConstraintRegistrantTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $aggregateRegistrant = new AggregateObjectConstraintRegistrant();
        $singleRegistrant = new class() implements IObjectConstraintRegistrant
        {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
            {
                $this->wasInvoked = true;
            }
        };
        $aggregateRegistrant->addConstraintRegistrant($singleRegistrant);
        $objectConstraints = new ObjectConstraintRegistry();
        $aggregateRegistrant->registerConstraints($objectConstraints);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
