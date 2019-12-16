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
use DateTime;
use Aphiria\Validation\Constraints\DateConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the date constraint
 */
class DateConstraintTest extends TestCase
{
    public function testEqualValuesPass(): void
    {
        $context = new ValidationContext($this);
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $constraint = new DateConstraint([$format1, $format2], 'foo');
        $this->assertTrue($constraint->passes((new DateTime)->format($format1), $context));
        $this->assertTrue($constraint->passes((new DateTime)->format($format2), $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new DateConstraint(['Ymd'], 'foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testUnequalValuesFail(): void
    {
        $context = new ValidationContext($this);
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $constraint = new DateConstraint([$format1, $format2], 'foo');
        $this->assertFalse($constraint->passes((new DateTime)->format('His'), $context));
        $this->assertFalse($constraint->passes((new DateTime)->format('Y'), $context));
    }
}
