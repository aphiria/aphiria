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
use Aphiria\Validation\Rules\RegexRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the regex rule
 */
class RegexRuleTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $rule = new RegexRule('/foo/', 'foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testMatchingValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new RegexRule('/^[a-z]{3}$/', 'foo');
        $this->assertTrue($rule->passes('foo', $context));
    }

    /**
     * Tests that non-matching values fail
     */
    public function testNonMatchingValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new RegexRule('/^[a-z]{3}$/', 'foo');
        $this->assertFalse($rule->passes('a', $context));
    }
}
