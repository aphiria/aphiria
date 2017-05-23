<?php
namespace Opulence\Routing\Matchers;

use Closure;

/**
 * Tests the closure route action
 */
class ClosureRouteActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var ClosureRouteAction An instance that uses a closure as the action */
    private $closureAction = null;
    /** @var Closure The closure used in the closure action */
    private $closure = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->closure = function () {
            // Don't do anything
        };
        $this->closureAction = new ClosureRouteAction($this->closure);
    }

    /**
     * Tests that the correct instance of the closure is returned by closure instances
     */
    public function testCorrectClosureInstanceIsReturned() : void
    {
        $this->assertSame($this->closure, $this->closureAction->getClosure());
    }

    /**
     * Tests that the method flag is set correctly
     */
    public function testMethodFlagSetCorrectly() : void
    {
        $this->assertFalse($this->closureAction->usesMethod());
    }
}
