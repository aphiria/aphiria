<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
        $this->assertSame('foo', $routeVariable->name);
        $this->assertSame($expectedConstraints, $routeVariable->constraints);
    }
}
