<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use PHPUnit\Framework\TestCase;

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
