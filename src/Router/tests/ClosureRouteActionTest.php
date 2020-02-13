<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\ClosureRouteAction;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * Tests the closure route action
 */
class ClosureRouteActionTest extends TestCase
{
    private ClosureRouteAction $closureAction;
    private Closure $closure;

    protected function setUp(): void
    {
        $this->closure = fn () => null;
        $this->closureAction = new ClosureRouteAction($this->closure);
    }

    public function testCorrectClosureInstanceIsReturned(): void
    {
        $this->assertSame($this->closure, $this->closureAction->closure);
    }

    public function testMethodFlagSetCorrectly(): void
    {
        $this->assertFalse($this->closureAction->usesMethod());
    }
}
