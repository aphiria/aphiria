<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned()
    {
        $this->assertEquals('regex', (new RegexRule('foo'))->getSlug());
    }
    
    /**
     * Tests that matching string pass
     */
    public function testMatchingStringsPass()
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertTrue($rule->passes('foo'));
    }
    
    /**
     * Tests non-matching strings fail
     */
    public function testNonMatchingStringsFail()
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertFalse($rule->passes('foobar'));
    }
}
