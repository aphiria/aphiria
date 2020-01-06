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
use Aphiria\Validation\Constraints\MaxConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the max constraint
 */
class MaxConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $context = new ValidationContext($this);
        $constraint = new MaxConstraint(1.5, true, 'foo');
        $this->assertFalse($constraint->passes(2, $context));
        $this->assertFalse($constraint->passes(1.6, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new MaxConstraint(1, true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new MaxConstraint(2, true, 'foo');
        $this->assertEquals(['value' => 'val', 'max' => 2], $constraint->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new MaxConstraint(2, true, 'foo');
        $this->assertTrue($constraint->passes(2, $context));
        $this->assertTrue($constraint->passes(1, $context));
        $this->assertTrue($constraint->passes(1.5, $context));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $context = new ValidationContext($this);
        $constraint = new MaxConstraint(2, false, 'foo');
        $this->assertFalse($constraint->passes(2, $context));
        $this->assertTrue($constraint->passes(1.9, $context));
    }
}
