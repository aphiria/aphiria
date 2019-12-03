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
        $rule = new CallbackRule();
        $rule->setArgs([$callback]);
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
        $passRule = new CallbackRule();
        $failRule = new CallbackRule();
        $passRule->setArgs([$trueCallback]);
        $failRule->setArgs([$falseCallback]);
        $this->assertTrue($passRule->passes('foo', $context));
        $this->assertFalse($failRule->passes('bar', $context));
    }

    public function testGettingSlug(): void
    {
        $rule = new CallbackRule();
        $this->assertEquals('callback', $rule->getSlug());
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $context = new ValidationContext($this);
        $this->expectException(LogicException::class);
        $rule = new CallbackRule();
        $rule->passes('foo', $context);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new CallbackRule();
        $rule->setArgs([]);
    }

    public function testPassingInvalidArg(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new CallbackRule();
        $rule->setArgs(['foo']);
    }
}
