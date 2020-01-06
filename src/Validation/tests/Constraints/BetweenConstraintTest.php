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
use Aphiria\Validation\Constraints\BetweenConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the between constraint
 */
class BetweenConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $context = new ValidationContext($this);
        $constraint = new BetweenConstraint(1, 2, true, true, 'foo');
        $this->assertFalse($constraint->passes(.9, $context));
        $this->assertFalse($constraint->passes(2.1, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true);
        $this->assertEquals(['value' => 'val', 'min' => 1, 'max' => 2], $constraint->getErrorMessagePlaceholders('val'));
    }

    public function testInclusiveFlagsAreRespected(): void
    {
        $context = new ValidationContext($this);
        $minInclusiveConstraint = new BetweenConstraint(1, 3, true, false);
        $this->assertTrue($minInclusiveConstraint->passes(1, $context));
        $this->assertTrue($minInclusiveConstraint->passes(2, $context));
        $this->assertFalse($minInclusiveConstraint->passes(0, $context));

        $maxInclusiveConstraint = new BetweenConstraint(1, 3, false, true);
        $this->assertTrue($maxInclusiveConstraint->passes(3, $context));
        $this->assertTrue($maxInclusiveConstraint->passes(2, $context));
        $this->assertFalse($maxInclusiveConstraint->passes(4, $context));

        $neitherInclusiveConstraint = new BetweenConstraint(1, 3, false, false);
        $this->assertFalse($neitherInclusiveConstraint->passes(1, $context));
        $this->assertFalse($neitherInclusiveConstraint->passes(3, $context));
        $this->assertTrue($neitherInclusiveConstraint->passes(2, $context));

        $bothInclusiveConstraint = new BetweenConstraint(1, 3, true, true);
        $this->assertTrue($bothInclusiveConstraint->passes(1, $context));
        $this->assertTrue($bothInclusiveConstraint->passes(3, $context));
        $this->assertTrue($bothInclusiveConstraint->passes(2, $context));
    }

    public function testNonNumericValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be numeric');
        $constraint = new BetweenConstraint(1, 2, true, true);
        $constraint->passes('foo', new ValidationContext('foo'));
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new BetweenConstraint(1, 2, true, true);
        $this->assertTrue($constraint->passes(1, $context));
        $this->assertTrue($constraint->passes(1.5, $context));
        $this->assertTrue($constraint->passes(2, $context));
    }
}
