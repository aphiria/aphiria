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
use Aphiria\Validation\Constraints\EqualsConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the equals constraint
 */
class EqualsConstraintTest extends TestCase
{
    public function testEqualValuesPass(): void
    {
        $context = new ValidationContext($this);
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertTrue($constraint->passes('foo', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertEquals('bar', $constraint->getErrorMessageId());
    }

    public function testUnequalValuesFail(): void
    {
        $context = new ValidationContext($this);
        $constraint = new EqualsConstraint('foo', 'bar');
        $this->assertFalse($constraint->passes('baz', $context));
    }
}
