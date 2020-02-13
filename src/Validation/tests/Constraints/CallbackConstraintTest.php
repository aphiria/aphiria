<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\CallbackConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the callback constraint
 */
class CallbackConstraintTest extends TestCase
{
    public function testCallbackIsExecuted(): void
    {
        $correctInputWasPassed = false;
        $callback = function ($value) use (&$correctInputWasPassed) {
            $correctInputWasPassed = $value === 'foo';

            return true;
        };
        $constraint = new CallbackConstraint($callback, 'foo');
        $constraint->passes('foo');
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
        $passConstraint = new CallbackConstraint($trueCallback, 'foo');
        $failConstraint = new CallbackConstraint($falseCallback, 'foo');
        $this->assertTrue($passConstraint->passes('foo'));
        $this->assertFalse($failConstraint->passes('bar'));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new CallbackConstraint(fn () => true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new CallbackConstraint(fn ($value) => true))->getErrorMessagePlaceholders('val'));
    }
}
