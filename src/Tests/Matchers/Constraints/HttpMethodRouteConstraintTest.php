<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Constraints;

use InvalidArgumentException;
use Opulence\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Opulence\Routing\Matchers\MatchedRouteCandidate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP method constraint
 */
class HttpMethodRouteConstraintTest extends TestCase
{
    public function testCreatingWithLowercaseStringNormalizesItToUppercase(): void
    {
        $this->assertEquals(['POST'], (new HttpMethodRouteConstraint('post'))->getAllowedMethods());
    }

    public function testCreatingWithNonStringNorArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Allowed methods must be a string or array of strings');
        new HttpMethodRouteConstraint(123);
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
        /** @var MatchedRouteCandidate $matchedRoute */
        $matchedRoute = $this->createMock(MatchedRouteCandidate::class);
        $this->assertTrue($constraint->passes($matchedRoute, 'GET', 'example.com', '/foo', []));
        $this->assertTrue($constraint->passes($matchedRoute, 'HEAD', 'example.com', '/foo', []));
        $this->assertFalse($constraint->passes($matchedRoute, 'POST', 'example.com', '/foo', []));
    }

    public function testPassesWorksOnLowercaseMethods(): void
    {
        $constraint = new HttpMethodRouteConstraint(['POST']);
        /** @var MatchedRouteCandidate $matchedRoute */
        $matchedRoute = $this->createMock(MatchedRouteCandidate::class);
        $this->assertTrue($constraint->passes($matchedRoute, 'post', 'example.com', '/', []));
    }
}
