<?php
namespace Opulence\Router\UriTemplates\Rules;

use InvalidArgumentException;

/**
 * Tests the between rule
 */
class BetweenRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned()
    {
        $this->assertEquals('between', (new BetweenRule(1, 2))->getSlug());
    }
    
    /**
     * Tests a failing value with an exclusive range
     */
    public function testFailingValueWithExclusiveRange()
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertFalse($rule->passes(3));
    }
    
    /**
     * Tests a failing value with an inclusive range
     */
    public function testFailingValueWithInclusiveRange()
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertFalse($rule->passes(3));
    }
    
    /**
     * Tests a passing value with an exclusive range
     */
    public function testPassingValueWithExclusiveRange()
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertTrue($rule->passes(1));
    }
    
    /**
     * Tests a passing value with an inclusive range
     */
    public function testPassingValueWithInclusiveRange()
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertTrue($rule->passes(2));
    }
    
    /**
     * Tests setting an invalid max value throws an exception
     */
    public function testInvalidMaxValueThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(1, false);
    }
    
    /**
     * Tests setting an invalid min value throws an exception
     */
    public function testInvalidMinValueThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(false, 1);
    }
}
