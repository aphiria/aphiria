<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\AggregateRouteRegistrant;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests the aggregate route registrant
 */
class AggregateRouteRegistrantTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $aggregateRegistrant = new AggregateRouteRegistrant();
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $this->wasInvoked = true;
            }
        };
        $aggregateRegistrant->addRouteRegistrant($singleRegistrant);
        $routes = new RouteCollection();
        $aggregateRegistrant->registerRoutes($routes);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
