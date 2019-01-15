<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Trees;

use Opulence\Routing\Matchers\Rules\IRule;
use Opulence\Routing\Matchers\Trees\RouteVariable;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route variable
 */
class RouteVariableTest extends TestCase
{
    public function testPropertiesAreSetFromConstructor(): void
    {
        $expectedRules = [$this->createMock(IRule::class)];
        $routeVariable = new RouteVariable('foo', $expectedRules);
        $this->assertEquals('foo', $routeVariable->name);
        $this->assertSame($expectedRules, $routeVariable->rules);
    }
}
