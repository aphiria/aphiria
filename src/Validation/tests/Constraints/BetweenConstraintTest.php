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
use PHPUnit\Framework\TestCase;

/**
 * Tests the between constraint
 */
class BetweenConstraintTest extends TestCase
{
    public function testFailingConstraint(): void
    {
        $context = new ValidationContext($this);
        $constraint = new BetweenConstraint(1, 2, true, 'foo');
        $this->assertFalse($constraint->passes(.9, $context));
        $this->assertFalse($constraint->passes(2.1, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorPlaceholders(): void
    {
        $constraint = new BetweenConstraint(1, 2, true, 'foo');
        $this->assertEquals(['min' => 1, 'max' => 2], $constraint->getErrorMessagePlaceholders());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new BetweenConstraint(1, 2, true, 'foo');
        $this->assertTrue($constraint->passes(1, $context));
        $this->assertTrue($constraint->passes(1.5, $context));
        $this->assertTrue($constraint->passes(2, $context));
    }

    public function testValueThatIsNotInclusive(): void
    {
        $context = new ValidationContext($this);
        $constraint = new BetweenConstraint(1, 2, false, 'foo');
        $this->assertFalse($constraint->passes(1, $context));
        $this->assertFalse($constraint->passes(2, $context));
        $this->assertTrue($constraint->passes(1.5, $context));
    }
}
