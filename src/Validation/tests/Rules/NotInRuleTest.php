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
use Aphiria\Validation\Rules\NotInRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the not-in-array rule
 */
class NotInRuleTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $rule = new NotInRule([], 'foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new NotInRule(['foo', 'bar'], 'foo');
        $this->assertTrue($rule->passes('baz', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new NotInRule(['foo', 'bar'], 'foo');
        $this->assertFalse($rule->passes('foo', $context));
    }
}
