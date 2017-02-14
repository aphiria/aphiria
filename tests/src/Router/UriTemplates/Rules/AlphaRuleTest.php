<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the alpha rule
 */
class AlphaRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that alphabet chars pass
     */
    public function testAlphaCharsPass()
    {
        $rule = new AlphaRule();
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('ab'));
    }
    
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned()
    {
        $this->assertEquals('alpha', (new AlphaRule)->getSlug());
    }
    
    /**
     * Tests that non-alphabet chars fail
     */
    public function testNonAlphaCharsFail()
    {
        $rule = new AlphaRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('1'));
        $this->assertFalse($rule->passes('a b'));
    }
}
