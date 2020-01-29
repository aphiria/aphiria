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
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ICU format error message interpolator
 */
class IcuFormatErrorMessageInterpolatorTest extends TestCase
{
    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithNoPlaceholders(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $this->assertEquals(
            'foo bar',
            $interpolator->interpolate('foo bar')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithFallbackLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator('de');
        $this->assertEquals(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithInputLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $this->assertEquals(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23], 'de')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithPlaceholders(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $this->assertEquals(
            'Dave has $1.23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testInterpolatingInvalidIcuMessageThrowsException(): void
    {
        $this->expectException(ErrorMessageInterpolationException::class);
        $this->expectExceptionMessage('Could not interpolate error message ID {');
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $interpolator->interpolate('{', [], 'en-US');
    }

    public function testSettingDefaultLocaleCausesInterpolationToUseItAsFallbackLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $interpolator->setDefaultLocale('de');
        $this->assertEquals(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }
}
