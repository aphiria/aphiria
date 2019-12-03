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
use Aphiria\Validation\Rules\InRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the in-array rule
 */
class InRuleTest extends TestCase
{
    public function testGettingSlug(): void
    {
        $rule = new InRule();
        $this->assertEquals('in', $rule->getSlug());
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new InRule();
        $rule->setArgs([['foo', 'bar']]);
        $this->assertTrue($rule->passes('foo', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new InRule();
        $rule->setArgs([['foo', 'bar']]);
        $this->assertFalse($rule->passes('baz', $context));
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $context = new ValidationContext($this);
        $this->expectException(LogicException::class);
        $rule = new InRule();
        $rule->passes('foo', $context);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new InRule();
        $rule->setArgs([]);
    }

    public function testPassingInvalidArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new InRule();
        $rule->setArgs([1]);
    }
}
