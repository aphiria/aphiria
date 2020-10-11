<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Constraints;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HttpMethodRouteConstraintTest extends TestCase
{
    public function testCreatingWithLowercaseStringNormalizesItToUppercase(): void
    {
        $this->assertEquals(['POST'], (new HttpMethodRouteConstraint('post'))->getAllowedMethods());
    }

    public function testCreatingWithStringParamConvertsToArrayOfAllowedMethods(): void
    {
        $this->assertEquals(['POST'], (new HttpMethodRouteConstraint('POST'))->getAllowedMethods());
    }

    public function testGettingAllowedMethodsForGetMethodIncludesHeadMethod(): void
    {
        $constraint = new HttpMethodRouteConstraint(['GET']);
        $this->assertEquals(['GET', 'HEAD'], $constraint->getAllowedMethods());
    }

    public function testGettingAllowedMethodsForNonGetMethodJustReturnsThatMethod(): void
    {
        $constraint = new HttpMethodRouteConstraint(['POST']);
        $this->assertEquals(['POST'], $constraint->getAllowedMethods());
    }

    public function testPassesOnlyReturnsTrueOnAllowedMethods(): void
    {
        $constraint = new HttpMethodRouteConstraint(['GET']);
        $matchedRoute = new MatchedRouteCandidate(
            new Route(new UriTemplate('foo'), new RouteAction('Foo', 'bar'), []),
            []
        );
        $this->assertTrue($constraint->passes($matchedRoute, 'GET', 'example.com', '/foo', []));
        $this->assertTrue($constraint->passes($matchedRoute, 'HEAD', 'example.com', '/foo', []));
        $this->assertFalse($constraint->passes($matchedRoute, 'POST', 'example.com', '/foo', []));
    }

    public function testPassesWorksOnLowercaseMethods(): void
    {
        $constraint = new HttpMethodRouteConstraint(['POST']);
        $matchedRoute = new MatchedRouteCandidate(
            new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []),
            []
        );
        $this->assertTrue($constraint->passes($matchedRoute, 'post', 'example.com', '/', []));
    }
}
