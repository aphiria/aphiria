<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\DateConstraint;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateConstraintTest extends TestCase
{
    public function testEmptyAcceptableFormatsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify at least one acceptable format');
        new DateConstraint([], 'foo');
    }

    public function testEqualValuesPass(): void
    {
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $constraint = new DateConstraint([$format1, $format2], 'foo');
        $this->assertTrue($constraint->passes((new DateTime())->format($format1)));
        $this->assertTrue($constraint->passes((new DateTime())->format($format2)));
    }

    public function testGettingErrorMessageId(): void
    {
        $constraint = new DateConstraint(['Ymd'], 'foo');
        $this->assertSame('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new DateConstraint(['Ymd']))->getErrorMessagePlaceholders('val'));
    }

    public function testUnequalValuesFail(): void
    {
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $constraint = new DateConstraint([$format1, $format2], 'foo');
        $this->assertFalse($constraint->passes((new DateTime())->format('His')));
        $this->assertFalse($constraint->passes((new DateTime())->format('Y')));
    }
}
