<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\ErrorMessageFormattingException;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ICU format error message formatter
 */
class IcuFormatErrorMessageFormatterTest extends TestCase
{
    public function testFormattingCorrectlyFormatsIcuFormattedErrorMessageIdWithNoPlaceholders(): void
    {
        $formatter = new IcuFormatErrorMessageFormatter();
        $this->assertEquals(
            'foo bar',
            $formatter->format('foo bar')
        );
    }

    public function testFormattingCorrectlyFormatsIcuFormattedErrorMessageIdWithFallbackLocale(): void
    {
        $formatter = new IcuFormatErrorMessageFormatter('de');
        $this->assertEquals(
            'Dave has $1,23',
            $formatter->format('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testFormattingCorrectlyFormatsIcuFormattedErrorMessageIdWithInputLocale(): void
    {
        $formatter = new IcuFormatErrorMessageFormatter();
        $this->assertEquals(
            'Dave has $1,23',
            $formatter->format('Dave has ${amount, number}', ['amount' => 1.23], 'de')
        );
    }

    public function testFormattingCorrectlyFormatsIcuFormattedErrorMessageIdWithPlaceholders(): void
    {
        $formatter = new IcuFormatErrorMessageFormatter();
        $this->assertEquals(
            'Dave has $1.23',
            $formatter->format('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testFormattingInvalidIcuMessageThrowsException(): void
    {
        $this->expectException(ErrorMessageFormattingException::class);
        $this->expectExceptionMessage('Could not format error message ID {');
        $formatter = new IcuFormatErrorMessageFormatter();
        $formatter->format('{', [], 'en-US');
    }
}
