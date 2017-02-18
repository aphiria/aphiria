<?php
namespace Opulence\Router\UriTemplates\Rules;

/**
 * Tests the UUIDV4 rule
 */
class UuidV4RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('uuidv4', UuidV4Rule::getSlug());
    }

    /**
     * Tests that a UUID passes
     */
    public function testMatchingStringsPass() : void
    {
        $rule = new UuidV4Rule();
        $string = \random_bytes(16);
        $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
        $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);
        $uuid = \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($string), 4));
        $this->assertTrue($rule->passes($uuid));
        $this->assertTrue($rule->passes('{' . $uuid . '}'));
    }

    /**
     * Tests non-UUID fails
     */
    public function testNonMatchingStringsFail() : void
    {
        $rule = new UuidV4Rule();
        $this->assertFalse($rule->passes('foo'));
    }
}
