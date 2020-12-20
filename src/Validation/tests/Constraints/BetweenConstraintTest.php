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

use Aphiria\Validation\Constraints\BetweenConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BetweenConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true, 'foo');
        $this->assertFalse($constraint->passes(.9, ));
        $this->assertFalse($constraint->passes(2.1));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true, 'foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true);
        $this->assertEquals(['value' => 'val', 'min' => 1, 'max' => 2], $constraint->getErrorMessagePlaceholders('val'));
    }

    public function testInclusiveFlagsAreRespected(): void
    {
        $minInclusiveConstraint = new BetweenConstraint(1, 3, true, false);
        $this->assertTrue($minInclusiveConstraint->passes(1));
        $this->assertTrue($minInclusiveConstraint->passes(2));
        $this->assertFalse($minInclusiveConstraint->passes(0));

        $maxInclusiveConstraint = new BetweenConstraint(1, 3, false, true);
        $this->assertTrue($maxInclusiveConstraint->passes(3));
        $this->assertTrue($maxInclusiveConstraint->passes(2));
        $this->assertFalse($maxInclusiveConstraint->passes(4));

        $neitherInclusiveConstraint = new BetweenConstraint(1, 3, false, false);
        $this->assertFalse($neitherInclusiveConstraint->passes(1));
        $this->assertFalse($neitherInclusiveConstraint->passes(3));
        $this->assertTrue($neitherInclusiveConstraint->passes(2));

        $bothInclusiveConstraint = new BetweenConstraint(1, 3, true, true);
        $this->assertTrue($bothInclusiveConstraint->passes(1));
        $this->assertTrue($bothInclusiveConstraint->passes(3));
        $this->assertTrue($bothInclusiveConstraint->passes(2));
    }

    public function testNonNumericValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be numeric');
        $constraint = new BetweenConstraint(1, 2, true, true);
        $constraint->passes('foo');
    }

    public function testPassingValue(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, true);
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.5));
        $this->assertTrue($constraint->passes(2));
    }
}
