<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use Opulence\Routing\Matchers\Rules\IntegerRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the integer rule
 */
class IntegerRuleTest extends TestCase
{
    public function testCorrectSlugIsReturned() : void
    {
        $this->assertEquals('int', IntegerRule::getSlug());
    }

    public function testFailingValue() : void
    {
        $rule = new IntegerRule();
        $this->assertFalse($rule->passes(false));
        $this->assertFalse($rule->passes('foo'));
        $this->assertFalse($rule->passes(1.5));
        $this->assertFalse($rule->passes('1.5'));
    }

    public function testPassingValue() : void
    {
        $rule = new IntegerRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(1));
    }
}
