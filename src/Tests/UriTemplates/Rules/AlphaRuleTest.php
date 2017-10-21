<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Tests\UriTemplates\Rules;

use Opulence\Routing\Matchers\UriTemplates\Rules\AlphaRule;

/**
 * Tests the alpha rule
 */
class AlphaRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that alphabet chars pass
     */
    public function testAlphaCharsPass() : void
    {
        $rule = new AlphaRule();
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('ab'));
    }

    /**
     * Tests that the correct slug is returned
     */
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('alpha', AlphaRule::getSlug());
    }

    /**
     * Tests that non-alphabet chars fail
     */
    public function testNonAlphaCharsFail() : void
    {
        $rule = new AlphaRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('1'));
        $this->assertFalse($rule->passes('a b'));
    }
}
