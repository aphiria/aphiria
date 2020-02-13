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

use Aphiria\Validation\Constraints\MinConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the min constraint
 */
class MinConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $constraint = new MinConstraint(1.5, true, 'foo');
        $this->assertFalse($constraint->passes(1));
        $this->assertFalse($constraint->passes(1.4));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new MinConstraint(1, true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new MinConstraint(2, true, 'foo');
        $this->assertEquals(['value' => 'val', 'min' => 2], $constraint->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $constraint = new MinConstraint(1, true, 'foo');
        $this->assertTrue($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.5));
        $this->assertTrue($constraint->passes(2));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $constraint = new MinConstraint(1, false, 'foo');
        $this->assertFalse($constraint->passes(1));
        $this->assertTrue($constraint->passes(1.1));
    }
}
