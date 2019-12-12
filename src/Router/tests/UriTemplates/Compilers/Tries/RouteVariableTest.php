<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route variable
 */
class RouteVariableTest extends TestCase
{
    public function testPropertiesAreSetFromConstructor(): void
    {
        $expectedConstraints = [$this->createMock(IRouteVariableConstraint::class)];
        $routeVariable = new RouteVariable('foo', $expectedConstraints);
        $this->assertEquals('foo', $routeVariable->name);
        $this->assertSame($expectedConstraints, $routeVariable->constraints);
    }
}
