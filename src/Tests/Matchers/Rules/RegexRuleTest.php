<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use Opulence\Routing\Matchers\Rules\RegexRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends TestCase
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
