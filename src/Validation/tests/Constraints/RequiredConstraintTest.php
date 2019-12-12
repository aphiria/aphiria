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
use Countable;
use Aphiria\Validation\Constraints\RequiredConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the required constraint
 */
class RequiredConstraintTest extends TestCase
{
    public function testEmptyArrayFails(): void
    {
        $context = new ValidationContext($this);
        $constraint = new RequiredConstraint('foo');
        $this->assertFalse($constraint->passes([], $context));
        $countable = $this->createMock(Countable::class);
        $countable->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->assertFalse($constraint->passes($countable, $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new RequiredConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testSetValuePasses(): void
    {
        $context = new ValidationContext($this);
        $constraint = new RequiredConstraint('foo');
        $this->assertTrue($constraint->passes(0, $context));
        $this->assertTrue($constraint->passes(true, $context));
        $this->assertTrue($constraint->passes(false, $context));
        $this->assertTrue($constraint->passes('foo', $context));
    }

    public function testUnsetValueFails(): void
    {
        $context = new ValidationContext($this);
        $constraint = new RequiredConstraint('foo');
        $this->assertFalse($constraint->passes(null, $context));
        $this->assertFalse($constraint->passes('', $context));
    }
}
