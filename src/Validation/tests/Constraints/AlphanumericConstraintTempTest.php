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

use Aphiria\Validation\Constraints\AlphanumericConstraintTemp;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alpha-numeric constraint
 */
class AlphanumericConstraintTempTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new AlphanumericConstraintTemp('foo');
        $this->assertFalse($constraint->passes('', $context));
        $this->assertFalse($constraint->passes('.', $context));
        $this->assertFalse($constraint->passes('a1 b', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new AlphanumericConstraintTemp('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $constraint = new AlphanumericConstraintTemp('foo');
        $this->assertTrue($constraint->passes('1', $context));
        $this->assertTrue($constraint->passes('a', $context));
        $this->assertTrue($constraint->passes('a1', $context));
        $this->assertTrue($constraint->passes('1abc', $context));
    }
}
