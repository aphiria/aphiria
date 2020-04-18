<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\IErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use PHPUnit\Framework\TestCase;

class StringReplaceErrorMessageInterpolatorTest extends TestCase
{
    public function testErrorMessageIdWithNoPlaceholdersIsReturnedIntact(): void
    {
        $interpolator = new StringReplaceErrorMessageInterpolator();
        $this->assertEquals('foo bar', $interpolator->interpolate('foo bar'));
    }

    public function testInterpolatingGetsErrorMessageTemplateFromRegistry(): void
    {
        $errorMessageTemplates = $this->createMock(IErrorMessageTemplateRegistry::class);
        $errorMessageTemplates->expects($this->once())
            ->method('getErrorMessageTemplate')
            ->with('foo', 'en-US')
            ->willReturn('bar');
        $interpolator = new StringReplaceErrorMessageInterpolator($errorMessageTemplates);
        $this->assertEquals('bar', $interpolator->interpolate('foo', [], 'en-US'));
    }

    public function testLeftoverUnusedPlaceholdersAreRemovedFromInterpolatedErrorMessage(): void
    {
        $interpolator = new StringReplaceErrorMessageInterpolator();
        $this->assertEquals('foo ', $interpolator->interpolate('foo {bar}'));
    }

    public function testPlaceholdersArePopulated(): void
    {
        $interpolator = new StringReplaceErrorMessageInterpolator();
        $this->assertEquals(
            'foo dave young',
            $interpolator->interpolate('foo {bar} {baz}', ['bar' => 'dave', 'baz' => 'young'])
        );
    }

    public function testSetDefaultLocaleDoesNotDoAnything(): void
    {
        $interpolator = new StringReplaceErrorMessageInterpolator();
        $interpolator->setDefaultLocale('foo');
        $this->assertEquals('foo dave', $interpolator->interpolate('foo {bar}', ['bar' => 'dave']));
    }
}
