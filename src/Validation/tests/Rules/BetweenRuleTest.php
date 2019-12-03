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
use Aphiria\Validation\Rules\BetweenRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the between rule
 */
class BetweenRuleTest extends TestCase
{
    public function testFailingRule(): void
    {
        $context = new ValidationContext($this);
        $rule = new BetweenRule();
        $rule->setArgs([1, 2]);
        $this->assertFalse($rule->passes(.9, $context));
        $this->assertFalse($rule->passes(2.1, $context));
    }

    public function testGettingErrorPlaceholders(): void
    {
        $rule = new BetweenRule();
        $rule->setArgs([1, 2]);
        $this->assertEquals(['min' => 1, 'max' => 2], $rule->getErrorPlaceholders());
    }

    public function testGettingSlug(): void
    {
        $rule = new BetweenRule();
        $this->assertEquals('between', $rule->getSlug());
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $context = new ValidationContext($this);
        $this->expectException(LogicException::class);
        $rule = new BetweenRule();
        $rule->passes(2, $context);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new BetweenRule();
        $rule->setArgs([]);
    }

    public function testPassingInvalidArg(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new BetweenRule();
        $rule->setArgs([
            function () {
            },
            function () {
            }
        ]);
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new BetweenRule();
        $rule->setArgs([1, 2]);
        $this->assertTrue($rule->passes(1, $context));
        $this->assertTrue($rule->passes(1.5, $context));
        $this->assertTrue($rule->passes(2, $context));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $context = new ValidationContext($this);
        $rule = new BetweenRule();
        $rule->setArgs([1, 2, false]);
        $this->assertFalse($rule->passes(1, $context));
        $this->assertFalse($rule->passes(2, $context));
        $this->assertTrue($rule->passes(1.5, $context));
    }
}
