<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers;

use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class RouteMatchingResultTest extends TestCase
{
    public function testMatchFoundIsFalseIfMatchedRouteIsNull(): void
    {
        $routeMatchingResult = new RouteMatchingResult(null, [], ['GET']);
        $this->assertFalse($routeMatchingResult->matchFound);
    }

    public function testMethodAllowedOnlyIfRouteIsNullAndAllowedMethodsIsPopulated(): void
    {
        $route = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $resultWithPopulatedRoute = new RouteMatchingResult($route, []);
        $this->assertTrue($resultWithPopulatedRoute->methodIsAllowed);
        $resultWithUnpopulatedRouteAndNoAllowedMethods = new RouteMatchingResult(null, []);
        $this->assertNull($resultWithUnpopulatedRouteAndNoAllowedMethods->methodIsAllowed);
        $resultWithUnpopulatedRouteAndAllowedMethods = new RouteMatchingResult(null, [], ['GET']);
        $this->assertFalse($resultWithUnpopulatedRouteAndAllowedMethods->methodIsAllowed);
    }

    public function testPropertiesSetInConstructor(): void
    {
        $expectedMatchedRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $expectedRouteVariables = ['foo' => 'bar'];
        $expectedAllowedMethods = ['GET'];
        $routeMatchingResult = new RouteMatchingResult(
            $expectedMatchedRoute,
            $expectedRouteVariables,
            $expectedAllowedMethods
        );
        $this->assertTrue($routeMatchingResult->matchFound);
        $this->assertSame($expectedMatchedRoute, $routeMatchingResult->route);
        $this->assertSame($expectedAllowedMethods, $routeMatchingResult->allowedMethods);
    }
}
