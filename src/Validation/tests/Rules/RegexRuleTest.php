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
use Aphiria\Validation\Rules\RegexRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends TestCase
{
    public function testGettingSlug(): void
    {
        $rule = new RegexRule();
        $this->assertEquals('regex', $rule->getSlug());
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new RegexRule();
        $rule->setArgs(['/^[a-z]{3}$/']);
        $this->assertTrue($rule->passes('foo', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new RegexRule();
        $rule->setArgs(['/^[a-z]{3}$/']);
        $this->assertFalse($rule->passes('a', $context));
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $context = new ValidationContext($this);
        $this->expectException(LogicException::class);
        $rule = new RegexRule();
        $rule->passes('foo', $context);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new RegexRule();
        $rule->setArgs([]);
    }

    public function testPassingInvalidArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new RegexRule();
        $rule->setArgs([1]);
    }
}
