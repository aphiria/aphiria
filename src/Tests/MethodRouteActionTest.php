<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Tests;

use Opulence\Routing\Matchers\MethodRouteAction;

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

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->methodAction = new MethodRouteAction(self::CLASS_NAME, self::METHOD_NAME);
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
