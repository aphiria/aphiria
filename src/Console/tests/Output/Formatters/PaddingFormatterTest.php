<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Output\Formatters\PaddingFormatter;
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
        $this->formatter->setPaddingString('+');
        $formattedText = $this->formatter->format($rows, fn ($row) => $row[0] . '-' . $row[1]);
        $this->assertEquals(
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
        $this->formatter->setPaddingString('+');
        $formattedText = $this->formatter->format($rows, fn ($row) => $row[0]);
        $this->assertEquals('a++' . PHP_EOL . 'cd+' . PHP_EOL . 'fg+' . PHP_EOL . 'ijk', $formattedText);
    }

    public function testCustomRowSeparatorWithRowArrays(): void
    {
        $rows = [
            ['a', '  b'],
            ['cd', ' ee'],
            [' fg ', 'hhh'],
            ['ijk', ' ll']
        ];
        $this->formatter->setEolChar('<br>');
        $formattedText = $this->formatter->format($rows, fn ($row) => $row[0] . '-' . $row[1]);
        $this->assertEquals('a  -b  <br>cd -ee <br>fg -hhh<br>ijk-ll ', $formattedText);
    }

    public function testCustomRowSeparatorWithStringRows(): void
    {
        $rows = [
            'a',
            'cd',
            ' fg ',
            'ijk'
        ];
        $this->formatter->setEolChar('<br>');
        $formattedText = $this->formatter->format($rows, fn ($row) => $row[0]);
        $this->assertEquals('a  <br>cd <br>fg <br>ijk', $formattedText);
    }

    public function testGettingEOLChar(): void
    {
        $this->formatter->setEolChar('foo');
        $this->assertEquals('foo', $this->formatter->getEolChar());
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
        $this->formatter->setPadAfter(true);
        $formattedRows = $this->formatter->format($rows, fn ($row) => $row[0] . '-' . $row[1]);
        $this->assertEquals(
            'a  -b  ' . PHP_EOL . 'cd -ee ' . PHP_EOL . 'fg -hhh' . PHP_EOL . 'ijk-ll ',
            $formattedRows
        );
        // Format with the padding before the string
        $this->formatter->setPadAfter(false);
        $formattedRows = $this->formatter->format($rows, fn ($row) => $row[0] . '-' . $row[1]);
        $this->assertEquals(
            '  a-  b' . PHP_EOL . ' cd- ee' . PHP_EOL . ' fg-hhh' . PHP_EOL . 'ijk- ll',
            $formattedRows
        );
    }

    public function testPaddingEmptyArray(): void
    {
        $this->assertEquals('', $this->formatter->format([], fn ($row) => $row[0]));
    }

    public function testPaddingSingleArray(): void
    {
        $this->assertEquals(
            'foo' . PHP_EOL . 'bar',
            $this->formatter->format(['  foo  ', 'bar'], fn ($row) => $row[0])
        );
    }

    public function testPaddingSingleString(): void
    {
        $this->assertEquals('foo', $this->formatter->format(['  foo  '], fn ($row) => $row[0]));
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
        $this->formatter->setPadAfter(true);
        $formattedRows = $this->formatter->format($rows, fn ($row) => $row[0]);
        $this->assertEquals('a  ' . PHP_EOL . 'cd ' . PHP_EOL . 'fg ' . PHP_EOL . 'ijk', $formattedRows);
        // Format with the padding before the string
        $this->formatter->setPadAfter(false);
        $formattedRows = $this->formatter->format($rows, fn ($row) => $row[0]);
        $this->assertEquals('  a' . PHP_EOL . ' cd' . PHP_EOL . ' fg' . PHP_EOL . 'ijk', $formattedRows);
    }
}
