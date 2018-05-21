<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Opulence\Serialization\Encoding\DateTimeEncoder;

/**
 * Tests the DateTime encoder
 */
class DateTimeEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DateTimeEncoder The encoder to test */
    private $dateTimeEncoder;

    public function setUp(): void
    {
        $this->dateTimeEncoder = new DateTimeEncoder();
    }

    public function testDecodingDateTimeCreatesDateTime(): void
    {
        $encodedValue = (new DateTime)->format(DateTime::ISO8601);
        $value = $this->dateTimeEncoder->decode($encodedValue, DateTime::class);
        $this->assertInstanceOf(DateTime::class, $value);
    }

    public function testDecodingDateTimeImmutableCreatesDateTimeImmutable(): void
    {
        $encodedValue = (new DateTimeImmutable)->format(DateTime::ISO8601);
        $value = $this->dateTimeEncoder->decode($encodedValue, DateTimeImmutable::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $value);
    }

    public function testDecodingDateTimeInterfaceCreatesDateTimeImmutable(): void
    {
        $encodedValue = (new DateTime)->format(DateTime::ISO8601);
        $value = $this->dateTimeEncoder->decode($encodedValue, DateTimeInterface::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $value);
    }

    public function testDecodingNonDateTimeTypesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dateTimeEncoder->decode(123, 'foo');
    }

    public function testEncodingDateTimeReturnsFormattedString(): void
    {
        $dateTime = new DateTime();
        $this->assertEquals($dateTime->format(DateTime::ISO8601), $this->dateTimeEncoder->encode($dateTime));
    }

    public function testEncodingNonDateTimeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dateTimeEncoder->encode('foo');
    }
}
