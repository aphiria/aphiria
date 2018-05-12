<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use DateTime;
use Opulence\Net\Http\Formatting\Serialization\DateTimeFormatter;

/**
 * Tests the DateTime formatter
 */
class DateTimeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DateTimeFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new DateTimeFormatter();
    }

    public function testDecodingDateTimeStringCreatesDateTimeFromFormattedString(): void
    {
        // I can't just look for equality to $expectedDateTime because fractions of a second aren't set in ISO-8601
        $expectedDateTime = new DateTime();
        $encodedValue = $expectedDateTime->format(DateTime::ISO8601);
        $decodedValue = $this->formatter->onDecoding($encodedValue, DateTime::class);
        $this->assertEquals($expectedDateTime->format(DateTime::ISO8601), $decodedValue->format(DateTime::ISO8601));
    }

    public function testDecodingTypeThatIsNotDateTimeJustReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onDecoding('foo', 'string'));
    }

    public function testEncodingDateTimeCreatesStringFromFormat(): void
    {
        $dateTime = new DateTime();
        $expectedEncodedValue = $dateTime->format(DateTime::ISO8601);
        $this->assertEquals($expectedEncodedValue, $this->formatter->onEncoding($dateTime, DateTime::class));
    }

    public function testEncodingTypeThatIsNotDateTimeJustReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onEncoding('foo', 'string'));
    }
}
