<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Rules;

use Opulence\Routing\UriTemplates\Rules\AlphanumericRule;

/**
 * Tests the alphanumeric rule
 */
class AlphanumericRuleTest extends \PHPUnit\Framework\TestCase
{
    public function testAlphanumericCharsPass(): void
    {
        $rule = new AlphanumericRule();
        $this->assertTrue($rule->passes('1'));
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('a1'));
        $this->assertTrue($rule->passes('1abc'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('alphanumeric', AlphanumericRule::getSlug());
    }

    public function testNonAlphanumericCharsFail(): void
    {
        $rule = new AlphanumericRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('.'));
        $this->assertFalse($rule->passes('a1 b'));
    }
}
