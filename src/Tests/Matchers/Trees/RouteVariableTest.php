<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests\Matchers\Trees;

use Aphiria\Routing\Matchers\Rules\IRule;
use Aphiria\Routing\Matchers\Trees\RouteVariable;
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
