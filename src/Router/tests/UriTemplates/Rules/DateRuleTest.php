<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Rules;

use Aphiria\Routing\UriTemplates\Rules\DateRule;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the date rule
 */
class DateRuleTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('date', DateRule::getSlug());
    }

    public function testFailingSingleFormat(): void
    {
        $format = 'F j';
        $rule = new DateRule($format);
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
    }

    public function testFailingMultipleFormats(): void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $rule = new DateRule([$format1, $format2]);
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
        $this->assertFalse($rule->passes((new DateTime)->format('Ymd')));
    }

    public function testEmptyListOfFormatsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('No formats specified for %s', DateRule::class));
        new DateRule([]);
    }

    public function testPassingSingleFormat(): void
    {
        $format = 'F j';
        $rule = new DateRule($format);
        $this->assertTrue($rule->passes((new DateTime)->format($format)));
    }

    public function testPassingMultipleFormats(): void
    {
        $format1 = 'F j';
        $format2 = 'j F';
        $rule = new DateRule([$format1, $format2]);
        $this->assertTrue($rule->passes((new DateTime)->format($format1)));
        $this->assertTrue($rule->passes((new DateTime)->format($format2)));
    }
}
