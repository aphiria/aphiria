<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\Formatters\PaddingFormatterOptions;
use PHPUnit\Framework\TestCase;

class PaddingFormatterTest extends TestCase
{
    private PaddingFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new PaddingFormatter();
    }

    public function testCustomPaddingStringWithArrayRows(): void
    {
        $rows = [
            ['a', 'b '],
            ['cd', ' ee'],
            [' fg ', 'hhh'],
            ['ijk', 'll ']
        ];
        $options = new PaddingFormatterOptions(paddingString: '+');
        $formattedText = $this->formatter->format($rows, fn (array $row): string => "{$row[0]}-{$row[1]}", $options);
        $this->assertSame(
            'a++-b++' . PHP_EOL . 'cd+-ee+' . PHP_EOL . 'fg+-hhh' . PHP_EOL . 'ijk-ll+',
            $formattedText
        );
    }

    public function testCustomPaddingStringWithStringRows(): void
    {
        $rows = [
            'a',
            'cd',
            ' fg ',
            'ijk'
        ];
        $options = new PaddingFormatterOptions(paddingString: '+');
        $formattedText = $this->formatter->format($rows, fn (array $row): string => (string)$row[0], $options);
        $this->assertSame('a++' . PHP_EOL . 'cd+' . PHP_EOL . 'fg+' . PHP_EOL . 'ijk', $formattedText);
    }

    public function testCustomRowSeparatorWithRowArrays(): void
    {
        $rows = [
            ['a', '  b'],
            ['cd', ' ee'],
            [' fg ', 'hhh'],
            ['ijk', ' ll']
        ];
        $options = new PaddingFormatterOptions(eolChar: '<br>');
        $formattedText = $this->formatter->format($rows, fn (array $row): string => "{$row[0]}-{$row[1]}", $options);
        $this->assertSame('a  -b  <br>cd -ee <br>fg -hhh<br>ijk-ll ', $formattedText);
    }

    public function testCustomRowSeparatorWithStringRows(): void
    {
        $rows = [
            'a',
            'cd',
            ' fg ',
            'ijk'
        ];
        $options = new PaddingFormatterOptions(eolChar: '<br>');
        $formattedText = $this->formatter->format($rows, fn (array $row): string => (string)$row[0], $options);
        $this->assertSame('a  <br>cd <br>fg <br>ijk', $formattedText);
    }

    public function testFormattingDoesNotAffectPadding(): void
    {
        $rows = [
            ['<b>a</b>'],
            ['aaa']
        ];
        $formattedRows = $this->formatter->format($rows, fn (array $row): string => (string)$row[0]);
        // Without the bold formatting, we should expect two padding spaces so that the widths of the texts are equal
        $this->assertEquals('<b>a</b>  ' . \PHP_EOL . 'aaa', $formattedRows);
    }

    public function testNormalizingColumns(): void
    {
        $rows = [
            ['a'],
            ['aa', 'bbbb'],
            ['aaa', 'bbb', 'ccc'],
            ['aaa', 'bbb', 'ccc', 'ddddd']
        ];
        $expected = [
            ['a', '', '', ''],
            ['aa', 'bbbb', '', ''],
            ['aaa', 'bbb', 'ccc', ''],
            ['aaa', 'bbb', 'ccc', 'ddddd']
        ];
        $this->assertEquals([3, 4, 3, 5], $this->formatter->normalizeColumns($rows));
        $this->assertEquals($expected, $rows);
    }

    public function testPaddingArrayRows(): void
    {
        $rows = [
            ['a', 'b'],
            ['cd', 'ee '],
            [' fg ', 'hhh'],
            ['ijk', ' ll']
        ];
        // Format with the padding after the string
        $options = new PaddingFormatterOptions(padAfter: true);
        $formattedRows = $this->formatter->format($rows, fn (array $row): string => "{$row[0]}-{$row[1]}", $options);
        $this->assertSame(
            'a  -b  ' . PHP_EOL . 'cd -ee ' . PHP_EOL . 'fg -hhh' . PHP_EOL . 'ijk-ll ',
            $formattedRows
        );
        // Format with the padding before the string
        $options = new PaddingFormatterOptions(padAfter: false);
        $formattedRows = $this->formatter->format($rows, fn (array $row): string => "{$row[0]}-{$row[1]}", $options);
        $this->assertSame(
            '  a-  b' . PHP_EOL . ' cd- ee' . PHP_EOL . ' fg-hhh' . PHP_EOL . 'ijk- ll',
            $formattedRows
        );
    }

    public function testPaddingEmptyArray(): void
    {
        $this->assertSame('', $this->formatter->format([], fn (array $row): string => (string)$row[0]));
    }

    public function testPaddingSingleArray(): void
    {
        $this->assertSame(
            'foo' . PHP_EOL . 'bar',
            $this->formatter->format(['  foo  ', 'bar'], fn (array $row): string => (string)$row[0])
        );
    }

    public function testPaddingSingleString(): void
    {
        $this->assertSame('foo', $this->formatter->format(['  foo  '], fn (array $row): string => (string)$row[0]));
    }

    public function testPaddingStringRows(): void
    {
        $rows = [
            'a',
            'cd',
            ' fg ',
            'ijk'
        ];
        // Format with the padding after the string
        $options = new PaddingFormatterOptions(padAfter: true);
        $formattedRows = $this->formatter->format($rows, fn (array $row): string => (string)$row[0], $options);
        $this->assertSame('a  ' . PHP_EOL . 'cd ' . PHP_EOL . 'fg ' . PHP_EOL . 'ijk', $formattedRows);
        // Format with the padding before the string
        $options = new PaddingFormatterOptions(padAfter: false);
        $formattedRows = $this->formatter->format($rows, fn (array $row): string => (string)$row[0], $options);
        $this->assertSame('  a' . PHP_EOL . ' cd' . PHP_EOL . ' fg' . PHP_EOL . 'ijk', $formattedRows);
    }
}
