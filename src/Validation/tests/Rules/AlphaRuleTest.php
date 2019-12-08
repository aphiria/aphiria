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

use Aphiria\Validation\Rules\AlphaRule;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alphabetic rule
 */
class AlphaRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new AlphaRule('foo');
        $this->assertFalse($rule->passes('', $context));
        $this->assertFalse($rule->passes('1', $context));
        $this->assertFalse($rule->passes('a b', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new AlphaRule('foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new AlphaRule('foo');
        $this->assertTrue($rule->passes('a', $context));
        $this->assertTrue($rule->passes('abc', $context));
    }
}
