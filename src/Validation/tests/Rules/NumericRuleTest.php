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

use Aphiria\Validation\Rules\NumericRule;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the numeric rule
 */
class NumericRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new NumericRule();
        $this->assertFalse($rule->passes(false, $context));
        $this->assertFalse($rule->passes('foo', $context));
    }

    public function testGettingSlug(): void
    {
        $rule = new NumericRule();
        $this->assertEquals('numeric', $rule->getSlug());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new NumericRule();
        $this->assertTrue($rule->passes(0, $context));
        $this->assertTrue($rule->passes(1, $context));
        $this->assertTrue($rule->passes(1.0, $context));
        $this->assertTrue($rule->passes('1.0', $context));
    }
}
