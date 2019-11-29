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
        $correctInputWasPassed = false;
        $callback = function ($value, array $inputs = []) use (&$correctInputWasPassed) {
            $correctInputWasPassed = $value === 'foo' && $inputs === ['bar' => 'baz'];

            return true;
        };
        $rule = new CallbackRule();
        $rule->setArgs([$callback]);
        $rule->passes('foo', ['bar' => 'baz']);
        $this->assertTrue($correctInputWasPassed);
    }

    public function testCallbackReturnValueIsRespected(): void
    {
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
        $this->assertTrue($passRule->passes('foo'));
        $this->assertFalse($failRule->passes('bar'));
    }

    public function testGettingSlug(): void
    {
        $rule = new CallbackRule();
        $this->assertEquals('callback', $rule->getSlug());
    }

    public function testNotSettingArgBeforePasses(): void
    {
        $this->expectException(LogicException::class);
        $rule = new CallbackRule();
        $rule->passes('foo');
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
