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
use Aphiria\Validation\Rules\CallbackRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the callback rule
 */
class CallbackRuleTest extends TestCase
{
    public function testCallbackIsExecuted(): void
    {
        $expectedContext = new ValidationContext($this);
        $correctInputWasPassed = false;
        $callback = function ($value, ValidationContext $validationContext) use (&$correctInputWasPassed, $expectedContext) {
            $correctInputWasPassed = $value === 'foo' && $validationContext === $expectedContext;

            return true;
        };
        $rule = new CallbackRule($callback, 'foo');
        $rule->passes('foo', $expectedContext);
        $this->assertTrue($correctInputWasPassed);
    }

    public function testCallbackReturnValueIsRespected(): void
    {
        $context = new ValidationContext($this);
        $trueCallback = function () {
            return true;
        };
        $falseCallback = function () {
            return false;
        };
        $passRule = new CallbackRule($trueCallback, 'foo');
        $failRule = new CallbackRule($falseCallback, 'foo');
        $this->assertTrue($passRule->passes('foo', $context));
        $this->assertFalse($failRule->passes('bar', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new CallbackRule(fn () => true, 'foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }
}
