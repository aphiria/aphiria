<?php
namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Tests the alphanumeric rule
 */
class AlphanumericRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that alphabet chars pass
     */
    public function testAlphanumericCharsPass() : void
    {
        $rule = new AlphanumericRule();
        $this->assertTrue($rule->passes('1'));
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('a1'));
        $this->assertTrue($rule->passes('1abc'));
    }

    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('alphanumeric', AlphanumericRule::getSlug());
    }

    /**
     * Tests that non-alphabet chars fail
     */
    public function testNonAlphanumericCharsFail() : void
    {
        $rule = new AlphanumericRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('.'));
        $this->assertFalse($rule->passes('a1 b'));
    }
}
