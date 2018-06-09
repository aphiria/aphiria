<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Rules;

use Opulence\Routing\UriTemplates\Rules\RegexRule;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('regex', RegexRule::getSlug());
    }

    public function testMatchingStringsPass(): void
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertTrue($rule->passes('foo'));
    }

    public function testNonMatchingStringsFail(): void
    {
        $rule = new RegexRule('/^[a-z]{3}$/');
        $this->assertFalse($rule->passes('foobar'));
    }
}
