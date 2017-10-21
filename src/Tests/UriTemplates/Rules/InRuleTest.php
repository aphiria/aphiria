<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Tests\UriTemplates\Rules;

use Opulence\Routing\Matchers\UriTemplates\Rules\InRule;

/**
 * Tests the in-array rule
 */
class InRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('in', InRule::getSlug());
    }

    /**
     * Tests that a value in the array passes
     */
    public function testValueInArrayPasses() : void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertTrue($rule->passes(1));
        $this->assertTrue($rule->passes(2));
        $this->assertTrue($rule->passes(3));
    }

    /**
     * Tests that a value not in the array fails
     */
    public function testValueNotInArrayFails() : void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertFalse($rule->passes(4));
        $this->assertFalse($rule->passes(5));
        $this->assertFalse($rule->passes(6));
    }
}
