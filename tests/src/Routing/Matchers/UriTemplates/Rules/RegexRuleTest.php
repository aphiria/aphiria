<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Rules;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('regex', RegexRule::getSlug());
    }

    /**
     * Tests that matching string pass
     */
    public function testMatchingStringsPass() : void
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertTrue($rule->passes('foo'));
    }

    /**
     * Tests non-matching strings fail
     */
    public function testNonMatchingStringsFail() : void
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertFalse($rule->passes('foobar'));
    }
}
