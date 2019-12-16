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

use Aphiria\Validation\Constraints\AlphaConstraint;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alphabetic constraint
 */
class AlphaConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new AlphaConstraint('foo');
        $this->assertFalse($constraint->passes('', $context));
        $this->assertFalse($constraint->passes('1', $context));
        $this->assertFalse($constraint->passes('a b', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new AlphaConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new AlphaConstraint('foo');
        $this->assertTrue($constraint->passes('a', $context));
        $this->assertTrue($constraint->passes('abc', $context));
    }
}
