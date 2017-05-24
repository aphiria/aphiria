<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Rules;

use InvalidArgumentException;

/**
 * Tests the between rule
 */
class BetweenRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('between', BetweenRule::getSlug());
    }

    /**
     * Tests a failing value with an exclusive range
     */
    public function testFailingValueWithExclusiveRange() : void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertFalse($rule->passes(3));
    }

    /**
     * Tests a failing value with an inclusive range
     */
    public function testFailingValueWithInclusiveRange() : void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertFalse($rule->passes(3));
    }

    /**
     * Tests a passing value with an exclusive range
     */
    public function testPassingValueWithExclusiveRange() : void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertTrue($rule->passes(1));
    }

    /**
     * Tests a passing value with an inclusive range
     */
    public function testPassingValueWithInclusiveRange() : void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertTrue($rule->passes(2));
    }

    /**
     * Tests setting an invalid max value throws an exception
     */
    public function testInvalidMaxValueThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(1, false);
    }

    /**
     * Tests setting an invalid min value throws an exception
     */
    public function testInvalidMinValueThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(false, 1);
    }
}
