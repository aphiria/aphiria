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

use Aphiria\Validation\ErrorMessages\ErrorMessageCompilationException;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ICU format error message compiler
 */
class IcuFormatErrorMessageCompilerTest extends TestCase
{
    public function testCompilingCorrectlyCompilesIcuFormattedErrorMessageIdWithNoPlaceholders(): void
    {
        $compiler = new IcuFormatErrorMessageCompiler();
        $this->assertEquals(
            'foo bar',
            $compiler->compile('foo bar')
        );
    }

    public function testCompilingCorrectlyCompilesIcuFormattedErrorMessageIdWithFallbackLocale(): void
    {
        $compiler = new IcuFormatErrorMessageCompiler('de');
        $this->assertEquals(
            'Dave has $1,23',
            $compiler->compile('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testCompilingCorrectlyCompilesIcuFormattedErrorMessageIdWithInputLocale(): void
    {
        $compiler = new IcuFormatErrorMessageCompiler();
        $this->assertEquals(
            'Dave has $1,23',
            $compiler->compile('Dave has ${amount, number}', ['amount' => 1.23], 'de')
        );
    }

    public function testCompilingCorrectlyCompilesIcuFormattedErrorMessageIdWithPlaceholders(): void
    {
        $compiler = new IcuFormatErrorMessageCompiler();
        $this->assertEquals(
            'Dave has $1.23',
            $compiler->compile('Dave has ${amount, number}', ['amount' => 1.23])
        );
    }

    public function testCompilingInvalidIcuMessageThrowsException(): void
    {
        $this->expectException(ErrorMessageCompilationException::class);
        $this->expectExceptionMessage('Could not compile error message ID {');
        $compiler = new IcuFormatErrorMessageCompiler();
        $compiler->compile('{', [], 'en-US');
    }
}
