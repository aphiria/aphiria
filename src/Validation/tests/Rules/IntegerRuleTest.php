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

use Aphiria\Validation\Rules\IntegerRule;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the integer rule
 */
class IntegerRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new IntegerRule('foo');
        $this->assertFalse($rule->passes(false, $context));
        $this->assertFalse($rule->passes('foo', $context));
        $this->assertFalse($rule->passes(1.5, $context));
        $this->assertFalse($rule->passes('1.5', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new IntegerRule('foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new IntegerRule('foo');
        $this->assertTrue($rule->passes(0, $context));
        $this->assertTrue($rule->passes(1, $context));
    }
}
