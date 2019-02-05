<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests;

use Closure;
use Aphiria\Routing\ClosureRouteAction;
use PHPUnit\Framework\TestCase;

/**
 * Tests the closure route action
 */
class ClosureRouteActionTest extends TestCase
{
    /** @var ClosureRouteAction An instance that uses a closure as the action */
    private $closureAction;
    /** @var Closure The closure used in the closure action */
    private $closure;

    public function setUp(): void
    {
        $this->closure = function () {
            // Don't do anything
        };
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
