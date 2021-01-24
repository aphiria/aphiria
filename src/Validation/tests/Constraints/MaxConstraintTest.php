<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\MaxConstraint;
use PHPUnit\Framework\TestCase;

class MaxConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $constraint = new MaxConstraint(1.5, true, 'foo');
        $this->assertFalse($constraint->passes(2));
        $this->assertFalse($constraint->passes(1.6));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new MaxConstraint(1, true, 'foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new MaxConstraint(2, true, 'foo');
        $this->assertEquals(['value' => 'val', 'max' => 2], $constraint->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new MaxConstraint(2, true, 'foo');
        $this->assertTrue($constraint->passes(2));
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.5));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $constraint = new MaxConstraint(2, false, 'foo');
        $this->assertFalse($constraint->passes(2));
        $this->assertTrue($constraint->passes(1.9));
    }
}
