<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Rules;

use Opulence\Routing\UriTemplates\Rules\InRule;

/**
 * Tests the in-array rule
 */
class InRuleTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('in', InRule::getSlug());
    }

    public function testValueInArrayPasses(): void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertTrue($rule->passes(1));
        $this->assertTrue($rule->passes(2));
        $this->assertTrue($rule->passes(3));
    }

    public function testValueNotInArrayFails(): void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertFalse($rule->passes(4));
        $this->assertFalse($rule->passes(5));
        $this->assertFalse($rule->passes(6));
    }
}
