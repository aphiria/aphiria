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

use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route registrant collection
 */
class RouteRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $registrants = new RouteRegistrantCollection();
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
        $registrants->add($singleRegistrant);
        $routes = new RouteCollection();
        $registrants->registerRoutes($routes);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }
}
