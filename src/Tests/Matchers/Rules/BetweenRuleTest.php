<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use InvalidArgumentException;
use Opulence\Routing\Matchers\Rules\BetweenRule;

/**
 * Tests the between rule
 */
class BetweenRuleTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('between', BetweenRule::getSlug());
    }

    public function testFailingValueWithExclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertFalse($rule->passes(3));
    }

    public function testFailingValueWithInclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertFalse($rule->passes(3));
    }

    public function testPassingValueWithExclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertTrue($rule->passes(1));
    }

    public function testPassingValueWithInclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertTrue($rule->passes(2));
    }

    public function testInvalidMaxValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(1, false);
    }

    public function testInvalidMinValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BetweenRule(false, 1);
    }
}
