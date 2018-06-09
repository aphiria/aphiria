<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\MethodRouteAction;

/**
 * Tests the method route action
 */
class MethodRouteActionTest extends \PHPUnit\Framework\TestCase
{
    /** @const The name of the class used in our method action */
    private const CLASS_NAME = 'Foo';
    /** @const The name of the method used in our method action */
    private const METHOD_NAME = 'bar';
    /** @var MethodRouteAction An instance that uses a method as the action */
    private $methodAction;

    public function setUp(): void
    {
        $this->methodAction = new MethodRouteAction(self::CLASS_NAME, self::METHOD_NAME);
    }

    public function testCorrectClassNameIsReturned(): void
    {
        $this->assertEquals(self::CLASS_NAME, $this->methodAction->getClassName());
    }

    public function testCorrectMethodNameIsReturned(): void
    {
        $this->assertEquals(self::METHOD_NAME, $this->methodAction->getMethodName());
    }

    public function testMethodFlagSetCorrectly(): void
    {
        $this->assertTrue($this->methodAction->usesMethod());
    }

    public function testNullClosureIsReturnedByMethodAction(): void
    {
        $this->assertNull($this->methodAction->getClosure());
    }
}
