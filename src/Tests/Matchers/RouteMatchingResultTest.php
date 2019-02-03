<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests\Matchers;

use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route matching result
 */
class RouteMatchingResultTest extends TestCase
{
    public function testMatchFoundIsFalseIfMatchedRouteIsNull(): void
    {
        $routeMatchingResult = new RouteMatchingResult(null, [], ['GET']);
        $this->assertFalse($routeMatchingResult->matchFound);
    }

    public function testMethodAllowedOnlyIfRouteIsNullAndAllowedMethodsIsPopulated(): void
    {
        $resultWithPopulatedRoute = new RouteMatchingResult($this->createMock(Route::class), []);
        $this->assertTrue($resultWithPopulatedRoute->methodIsAllowed);
        $resultWithUnpopulatedRouteAndNoAllowedMethods = new RouteMatchingResult(null, []);
        $this->assertNull($resultWithUnpopulatedRouteAndNoAllowedMethods->methodIsAllowed);
        $resultWithUnpopulatedRouteAndAllowedMethods = new RouteMatchingResult(null, [], ['GET']);
        $this->assertFalse($resultWithUnpopulatedRouteAndAllowedMethods->methodIsAllowed);
    }

    public function testPropertiesSetInConstructor(): void
    {
        /** @var Route|MockObject $expectedMatchedRoute */
        $expectedMatchedRoute = $this->createMock(Route::class);
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
