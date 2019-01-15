<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Closure;
use Opulence\Routing\ClosureRouteAction;
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
