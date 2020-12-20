<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\CallbackConstraint;
use PHPUnit\Framework\TestCase;

class CallbackConstraintTest extends TestCase
{
    public function testCallbackIsExecuted(): void
    {
        $correctInputWasPassed = false;
        $callback = function (mixed $value) use (&$correctInputWasPassed): bool {
            $correctInputWasPassed = $value === 'foo';

            return true;
        };
        $constraint = new CallbackConstraint($callback, 'foo');
        $constraint->passes('foo');
        $this->assertTrue($correctInputWasPassed);
    }

    public function testCallbackReturnValueIsRespected(): void
    {
        $trueCallback = function (): bool {
            return true;
        };
        $falseCallback = function (): bool {
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
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new CallbackConstraint(fn (mixed $value) => true))->getErrorMessagePlaceholders('val'));
    }
}
