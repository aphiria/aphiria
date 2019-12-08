<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules;

use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\Rules\MaxRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the max rule
 */
class MaxRuleTest extends TestCase
{
    public function testFailingRule(): void
    {
        $context = new ValidationContext($this);
        $rule = new MaxRule(1.5, true, 'foo');
        $this->assertFalse($rule->passes(2, $context));
        $this->assertFalse($rule->passes(1.6, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new MaxRule(1, true, 'foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $rule = new MaxRule(2, true, 'foo');
        $this->assertEquals(['max' => 2], $rule->getErrorMessagePlaceholders());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new MaxRule(2, true, 'foo');
        $this->assertTrue($rule->passes(2, $context));
        $this->assertTrue($rule->passes(1, $context));
        $this->assertTrue($rule->passes(1.5, $context));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $context = new ValidationContext($this);
        $rule = new MaxRule(2, false, 'foo');
        $this->assertFalse($rule->passes(2, $context));
        $this->assertTrue($rule->passes(1.9, $context));
    }
}
