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

use Aphiria\Validation\ErrorMessages\ErrorMessageInterpolationException;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolater;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ICU format error message interpolater
 */
class IcuFormatErrorMessageInterpolaterTest extends TestCase
{
    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithNoPlaceholders(): void
    {
        $interpolater = new IcuFormatErrorMessageInterpolater();
        $this->assertEquals(
            'foo bar',
            $interpolater->interpolate('foo bar')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithFallbackLocale(): void
    {
        $interpolater = new IcuFormatErrorMessageInterpolater('de');
        $this->assertEquals(
            'Dave has $1,23',
            $interpolater->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithInputLocale(): void
    {
        $interpolater = new IcuFormatErrorMessageInterpolater();
        $this->assertEquals(
            'Dave has $1,23',
            $interpolater->interpolate('Dave has ${amount, number}', ['amount' => 1.23], 'de')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithPlaceholders(): void
    {
        $interpolater = new IcuFormatErrorMessageInterpolater();
        $this->assertEquals(
            'Dave has $1.23',
            $interpolater->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testInterpolatingInvalidIcuMessageThrowsException(): void
    {
        $this->expectException(ErrorMessageInterpolationException::class);
        $this->expectExceptionMessage('Could not interpolate error message ID {');
        $interpolater = new IcuFormatErrorMessageInterpolater();
        $interpolater->interpolate('{', [], 'en-US');
    }

    public function testSettingDefaultLocaleCausesInterpolationToUseItAsFallbackLocale(): void
    {
        $interpolater = new IcuFormatErrorMessageInterpolater();
        $interpolater->setDefaultLocale('de');
        $this->assertEquals(
            'Dave has $1,23',
            $interpolater->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }
}
