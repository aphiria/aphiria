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
use InvalidArgumentException;
use LogicException;
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
        $rule = new MaxRule();
        $rule->setArgs([1.5]);
        $this->assertFalse($rule->passes(2, $context));
        $this->assertFalse($rule->passes(1.6, $context));
    }

    public function testGettingErrorPlaceholders(): void
    {
        $rule = new MaxRule();
        $rule->setArgs([2]);
        $this->assertEquals(['max' => 2], $rule->getErrorPlaceholders());
    }

    public function testGettingSlug(): void
    {
        $rule = new MaxRule();
        $this->assertEquals('max', $rule->getSlug());
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $context = new ValidationContext($this);
        $this->expectException(LogicException::class);
        $rule = new MaxRule();
        $rule->passes(2, $context);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new MaxRule();
        $rule->setArgs([]);
    }

    public function testPassingInvalidArg(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new MaxRule();
        $rule->setArgs([
            function () {
            }
        ]);
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new MaxRule();
        $rule->setArgs([2]);
        $this->assertTrue($rule->passes(2, $context));
        $this->assertTrue($rule->passes(1, $context));
        $this->assertTrue($rule->passes(1.5, $context));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $context = new ValidationContext($this);
        $rule = new MaxRule();
        $rule->setArgs([2, false]);
        $this->assertFalse($rule->passes(2, $context));
        $this->assertTrue($rule->passes(1.9, $context));
    }
}
