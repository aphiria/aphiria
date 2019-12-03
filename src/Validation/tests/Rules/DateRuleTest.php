<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules;

use Aphiria\Validation\ValidationContext;
use DateTime;
use InvalidArgumentException;
use Aphiria\Validation\Rules\DateRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the date rule
 */
class DateRuleTest extends TestCase
{
    public function testEqualValuesPass(): void
    {
        $context = new ValidationContext($this);
        $rule = new DateRule();
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $rule->setArgs([$format1]);
        $this->assertTrue($rule->passes((new DateTime)->format($format1), $context));
        $rule->setArgs([[$format1, $format2]]);
        $this->assertTrue($rule->passes((new DateTime)->format($format2), $context));
    }

    public function testGettingSlug(): void
    {
        $rule = new DateRule();
        $this->assertEquals('date', $rule->getSlug());
    }

    public function testInvalidArgType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new DateRule();
        $rule->setArgs([1]);
    }

    public function testPassingEmptyArgArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $rule = new DateRule();
        $rule->setArgs([]);
    }

    public function testUnequalValuesFail(): void
    {
        $context = new ValidationContext($this);
        $rule = new DateRule();
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $rule->setArgs([$format1]);
        $this->assertFalse($rule->passes((new DateTime)->format('His'), $context));
        $rule->setArgs([[$format1, $format2]]);
        $this->assertFalse($rule->passes((new DateTime)->format('Y'), $context));
    }
}
