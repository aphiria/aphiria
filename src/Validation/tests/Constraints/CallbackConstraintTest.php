<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\ValidationContext;
use Aphiria\Validation\Constraints\CallbackConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the callback constraint
 */
class CallbackConstraintTest extends TestCase
{
    public function testCallbackIsExecuted(): void
    {
        $expectedContext = new ValidationContext($this);
        $correctInputWasPassed = false;
        $callback = function ($value, ValidationContext $validationContext) use (&$correctInputWasPassed, $expectedContext) {
            $correctInputWasPassed = $value === 'foo' && $validationContext === $expectedContext;

            return true;
        };
        $constraint = new CallbackConstraint($callback, 'foo');
        $constraint->passes('foo', $expectedContext);
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
        $passConstraint = new CallbackConstraint($trueCallback, 'foo');
        $failConstraint = new CallbackConstraint($falseCallback, 'foo');
        $this->assertTrue($passConstraint->passes('foo', $context));
        $this->assertFalse($failConstraint->passes('bar', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new CallbackConstraint(fn () => true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }
}
