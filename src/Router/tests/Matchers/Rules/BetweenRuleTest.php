<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Rules;

use Aphiria\Routing\Matchers\Rules\BetweenRule;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the between rule
 */
class BetweenRuleTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('between', BetweenRule::getSlug());
    }

    public function testFailingValueWithExclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertFalse($rule->passes(3));
    }

    public function testFailingValueWithInclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertFalse($rule->passes(3));
    }

    public function testPassingValueWithExclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, false);
        $this->assertTrue($rule->passes(1));
    }

    public function testPassingValueWithInclusiveRange(): void
    {
        $rule = new BetweenRule(0, 2, true);
        $this->assertTrue($rule->passes(2));
    }

    public function testInvalidMaxValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max value must be numeric');
        new BetweenRule(1, false);
    }

    public function testInvalidMinValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min value must be numeric');
        new BetweenRule(false, 1);
    }
}
