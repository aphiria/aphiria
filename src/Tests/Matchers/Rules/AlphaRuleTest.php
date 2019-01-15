<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use Opulence\Routing\Matchers\Rules\AlphaRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alpha rule
 */
class AlphaRuleTest extends TestCase
{
    public function testAlphaCharsPass(): void
    {
        $rule = new AlphaRule();
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('ab'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('alpha', AlphaRule::getSlug());
    }

    public function testNonAlphaCharsFail(): void
    {
        $rule = new AlphaRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('1'));
        $this->assertFalse($rule->passes('a b'));
    }
}
