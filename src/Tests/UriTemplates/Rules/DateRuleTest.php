<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Rules;

use DateTime;
use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Rules\DateRule;

/**
 * Tests the date rule
 */
class DateRuleTest extends \PHPUnit\Framework\TestCase
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
