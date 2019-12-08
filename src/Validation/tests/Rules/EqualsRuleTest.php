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
use Aphiria\Validation\Rules\EqualsRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the equals rule
 */
class EqualsRuleTest extends TestCase
{
    public function testEqualValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new EqualsRule('foo', 'bar');
        $this->assertTrue($rule->passes('foo', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new EqualsRule('foo', 'bar');
        $this->assertEquals('bar', $rule->getErrorMessageId());
    }

    public function testUnequalValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new EqualsRule('foo', 'bar');
        $this->assertFalse($rule->passes('baz', $context));
    }
}
