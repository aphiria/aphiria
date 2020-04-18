<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\DateConstraint;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateConstraintTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('date', DateConstraint::getSlug());
    }

    public function testFailingSingleFormat(): void
    {
        $format = 'F j';
        $constraint = new DateConstraint($format);
        $this->assertFalse($constraint->passes((new DateTime())->format('Ymd')));
    }

    public function testFailingMultipleFormats(): void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $constraint = new DateConstraint([$format1, $format2]);
        $this->assertFalse($constraint->passes((new DateTime())->format('Ymd')));
        $this->assertFalse($constraint->passes((new DateTime())->format('Ymd')));
    }

    public function testEmptyListOfFormatsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('No formats specified for %s', DateConstraint::class));
        new DateConstraint([]);
    }

    public function testPassingSingleFormat(): void
    {
        $format = 'F j';
        $constraint = new DateConstraint($format);
        $this->assertTrue($constraint->passes((new DateTime())->format($format)));
    }

    public function testPassingMultipleFormats(): void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $constraint = new DateConstraint([$format1, $format2]);
        $this->assertTrue($constraint->passes((new DateTime())->format($format1)));
        $this->assertTrue($constraint->passes((new DateTime())->format($format2)));
    }
}
