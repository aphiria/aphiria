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
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $rule = new DateRule([$format1, $format2], 'foo');
        $this->assertTrue($rule->passes((new DateTime)->format($format1), $context));
        $this->assertTrue($rule->passes((new DateTime)->format($format2), $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new DateRule(['Ymd'], 'foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testUnequalValuesFail(): void
    {
        $context = new ValidationContext($this);
        $format1 = 'F j';
        $format2 = 's:i:H d-m-Y';
        $rule = new DateRule([$format1, $format2], 'foo');
        $this->assertFalse($rule->passes((new DateTime)->format('His'), $context));
        $this->assertFalse($rule->passes((new DateTime)->format('Y'), $context));
    }
}
