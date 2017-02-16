<?php
namespace Opulence\Router;

use Closure;
use SuperClosure\SerializerInterface;

/**
 * Tests the closure route action
 */
class ClosureRouteActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var ClosureRouteAction An instance that uses a closure as the action */
    private $closureAction = null;
    /** @var Closure The closure used in the closure action */
    private $closure = null;
    /** @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject The mock serializer used by our actions */
    private $serializer = null;
    
    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->closure = function () {
            // Don't do anything
        };
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->closureAction = new ClosureRouteAction($this->closure, $this->serializer);
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
