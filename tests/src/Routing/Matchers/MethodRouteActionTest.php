<?php
namespace Opulence\Routing\Matchers;

use SuperClosure\SerializerInterface;

/**
 * Tests the method route action
 */
class MethodRouteActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var The name of the class used in our method action */
    private const CLASS_NAME = 'Foo';
    /** @var The name of the method used in our method action */
    private const METHOD_NAME = 'bar';
    /** @var MethodRouteAction An instance that uses a method as the action */
    private $methodAction = null;
    /** @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject The mock serializer used by our actions */
    private $serializer = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->methodAction = new MethodRouteAction(self::CLASS_NAME, self::METHOD_NAME, $this->serializer);
    }

    /**
     * Tests the correct class name is returned
     */
    public function testCorrectClassNameIsReturned() : void
    {
        $this->assertEquals(self::CLASS_NAME, $this->methodAction->getClassName());
    }

    /**
     * Tests the correct method name is returned
     */
    public function testCorrectMethodNameIsReturned() : void
    {
        $this->assertEquals(self::METHOD_NAME, $this->methodAction->getMethodName());
    }

    /**
     * Tests that the method flag is set correctly
     */
    public function testMethodFlagSetCorrectly() : void
    {
        $this->assertTrue($this->methodAction->usesMethod());
    }

    /**
     * Tests that the closure is null when using a method action
     */
    public function testNullClosureIsReturnedByMethodAction() : void
    {
        $this->assertNull($this->methodAction->getClosure());
    }
}
