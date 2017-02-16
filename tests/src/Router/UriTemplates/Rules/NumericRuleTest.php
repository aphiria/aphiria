<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the numeric rule
 */
class NumericRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that alphabet chars pass
     */
    public function testAlphaCharsPass() : void
    {
        $rule = new NumericRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(1));
        $this->assertTrue($rule->passes(1.0));
        $this->assertTrue($rule->passes('1.0'));
    }
    
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('numeric', (new NumericRule)->getSlug());
    }
    
    /**
     * Tests that non-alphabet chars fail
     */
    public function testNonAlphaCharsFail() : void
    {
        $rule = new NumericRule();
        $this->assertFalse($rule->passes(false));
        $this->assertFalse($rule->passes('foo'));
    }
}
