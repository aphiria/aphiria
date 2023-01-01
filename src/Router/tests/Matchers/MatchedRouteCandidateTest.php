<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers;

use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class MatchedRouteCandidateTest extends TestCase
{
    public function testPropertiesSetCorrectlyInConstructor(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(new UriTemplate(''), new RouteAction($controller::class, 'bar'), []);
        $expectedRouteVariables = ['foo' => 'bar'];
        $matchedRoute = new MatchedRouteCandidate($expectedRoute, $expectedRouteVariables);
        $this->assertSame($expectedRoute, $matchedRoute->route);
        $this->assertSame($expectedRouteVariables, $matchedRoute->routeVariables);
    }
}
