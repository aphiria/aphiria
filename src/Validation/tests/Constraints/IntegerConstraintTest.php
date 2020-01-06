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

use Aphiria\Validation\Constraints\IntegerConstraint;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the integer constraint
 */
class IntegerConstraintTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new IntegerConstraint('foo');
        $this->assertFalse($constraint->passes(false, $context));
        $this->assertFalse($constraint->passes('foo', $context));
        $this->assertFalse($constraint->passes(1.5, $context));
        $this->assertFalse($constraint->passes('1.5', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new IntegerConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new IntegerConstraint)->getErrorMessagePlaceholders('val'));
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new IntegerConstraint('foo');
        $this->assertTrue($constraint->passes(0, $context));
        $this->assertTrue($constraint->passes(1, $context));
    }
}
