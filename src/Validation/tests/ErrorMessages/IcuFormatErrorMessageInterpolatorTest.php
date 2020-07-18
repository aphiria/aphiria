<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\ErrorMessageInterpolationException;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageTemplateRegistry;
use PHPUnit\Framework\TestCase;

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
        $interpolator = new IcuFormatErrorMessageInterpolator(null, 'de');
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

    public function testInterpolatingGetsErrorMessageTemplateFromRegistry(): void
    {
        $errorMessageTemplates = $this->createMock(IErrorMessageTemplateRegistry::class);
        $errorMessageTemplates->expects($this->once())
            ->method('getErrorMessageTemplate')
            ->with('foo', 'en-US')
            ->willReturn('bar');
        $interpolator = new IcuFormatErrorMessageInterpolator($errorMessageTemplates);
        $this->assertEquals('bar', $interpolator->interpolate('foo', [], 'en-US'));
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
