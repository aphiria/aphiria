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

use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraints registrant collection
 */
class ObjectConstraintsRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $aggregateRegistrant = new ObjectConstraintsRegistrantCollection();
        $singleRegistrant = new class() implements IObjectConstraintsRegistrant
        {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
            {
                $this->wasInvoked = true;
            }
        };
        $aggregateRegistrant->add($singleRegistrant);
        $objectConstraints = new ObjectConstraintsRegistry();
        $aggregateRegistrant->registerConstraints($objectConstraints);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
