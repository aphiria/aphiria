<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Constraints;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class HttpMethodRouteConstraintTest extends TestCase
{
    public function testCreatingWithLowercaseStringNormalizesItToUppercase(): void
    {
        $this->assertEquals(['POST'], new HttpMethodRouteConstraint('post')->allowedMethods);
    }

    public function testCreatingWithStringParamConvertsToArrayOfAllowedMethods(): void
    {
        $this->assertEquals(['POST'], new HttpMethodRouteConstraint('POST')->allowedMethods);
    }

    public function testGettingAllowedMethodsForGetMethodIncludesHeadMethod(): void
    {
        $constraint = new HttpMethodRouteConstraint(['GET']);
        $this->assertEquals(['GET', 'HEAD'], $constraint->allowedMethods);
    }

    public function testGettingAllowedMethodsForNonGetMethodJustReturnsThatMethod(): void
    {
        $constraint = new HttpMethodRouteConstraint(['POST']);
        $this->assertEquals(['POST'], $constraint->allowedMethods);
    }

    public function testPassesOnlyReturnsTrueOnAllowedMethods(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraint = new HttpMethodRouteConstraint(['GET']);
        $matchedRoute = new MatchedRouteCandidate(
            new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), []),
            []
        );
        $this->assertTrue($constraint->passes($matchedRoute, 'GET', 'example.com', '/foo', []));
        $this->assertTrue($constraint->passes($matchedRoute, 'HEAD', 'example.com', '/foo', []));
        $this->assertFalse($constraint->passes($matchedRoute, 'POST', 'example.com', '/foo', []));
    }

    public function testPassesWorksOnLowercaseMethods(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraint = new HttpMethodRouteConstraint(['POST']);
        $matchedRoute = new MatchedRouteCandidate(
            new Route(new UriTemplate(''), new RouteAction($controller::class, 'bar'), []),
            []
        );
        $this->assertTrue($constraint->passes($matchedRoute, 'post', 'example.com', '/', []));
    }
}
