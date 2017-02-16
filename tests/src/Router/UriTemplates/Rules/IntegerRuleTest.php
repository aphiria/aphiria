<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the integer rule
 */
class IntegerRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('int', (new IntegerRule)->getSlug());
    }
    
    /**
     * Tests that a failing value
     */
    public function testFailingValue() : void
    {
        $rule = new IntegerRule();
        $this->assertFalse($rule->passes(false));
        $this->assertFalse($rule->passes('foo'));
        $this->assertFalse($rule->passes(1.5));
        $this->assertFalse($rule->passes('1.5'));
    }
    
    /**
     * Tests a passing value
     */
    public function testPassingValue() : void
    {
        $rule = new IntegerRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(1));
    }
}
