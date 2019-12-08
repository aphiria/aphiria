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
use Countable;
use Aphiria\Validation\Rules\RequiredRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the required rule
 */
class RequiredRuleTest extends TestCase
{
    public function testEmptyArrayFails(): void
    {
        $context = new ValidationContext($this);
        $rule = new RequiredRule('foo');
        $this->assertFalse($rule->passes([], $context));
        $countable = $this->createMock(Countable::class);
        $countable->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->assertFalse($rule->passes($countable, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new RequiredRule('foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testSetValuePasses(): void
    {
        $context = new ValidationContext($this);
        $rule = new RequiredRule('foo');
        $this->assertTrue($rule->passes(0, $context));
        $this->assertTrue($rule->passes(true, $context));
        $this->assertTrue($rule->passes(false, $context));
        $this->assertTrue($rule->passes('foo', $context));
    }

    public function testUnsetValueFails(): void
    {
        $context = new ValidationContext($this);
        $rule = new RequiredRule('foo');
        $this->assertFalse($rule->passes(null, $context));
        $this->assertFalse($rule->passes('', $context));
    }
}
