<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\MinConstraint;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('foo', $constraint->errorMessageId);
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
