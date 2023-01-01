<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
        $this->assertSame(
            'foo bar',
            $interpolator->interpolate('foo bar')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithFallbackLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator(defaultLocale: 'de');
        $this->assertSame(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithInputLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $this->assertSame(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23], 'de')
        );
    }

    public function testInterpolatingCorrectlyFormatsIcuFormattedErrorMessageIdWithPlaceholders(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $this->assertSame(
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
        $this->assertSame('bar', $interpolator->interpolate('foo', [], 'en-US'));
    }

    public function testSettingDefaultLocaleCausesInterpolationToUseItAsFallbackLocale(): void
    {
        $interpolator = new IcuFormatErrorMessageInterpolator();
        $interpolator->setDefaultLocale('de');
        $this->assertSame(
            'Dave has $1,23',
            $interpolator->interpolate('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }
}
