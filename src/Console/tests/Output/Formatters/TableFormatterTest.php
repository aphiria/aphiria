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

use Aphiria\Console\Output\Formatters\TableFormatter;
use Aphiria\Console\Output\Formatters\TableFormatterOptions;
use PHPUnit\Framework\TestCase;

class TableFormatterTest extends TestCase
{
    private TableFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new TableFormatter();
    }

    public function testFormattingEmptyTable(): void
    {
        $this->assertEmpty($this->formatter->format([]));
    }

    public function testFormattingSingleHeaderAndColumn(): void
    {
        $headers = ['foo'];
        $rows = [['a']];
        $expected =
            '+-----+' . PHP_EOL .
            '| foo |' . PHP_EOL .
            '+-----+' . PHP_EOL .
            '| a   |' . PHP_EOL .
            '+-----+';
        $this->assertSame($expected, $this->formatter->format($rows, $headers));
    }

    public function testFormattingSingleRow(): void
    {
        $rows = [['a', 'bb', 'ccc']];
        $expected =
            '+---+----+-----+' . PHP_EOL .
            '| a | bb | ccc |' . PHP_EOL .
            '+---+----+-----+';
        $this->assertSame($expected, $this->formatter->format($rows));
    }

    public function testFormattingSingleRowAndColumn(): void
    {
        $rows = [['a']];
        $expected =
            '+---+' . PHP_EOL .
            '| a |' . PHP_EOL .
            '+---+';
        $this->assertSame($expected, $this->formatter->format($rows));
    }

    public function testFormattingTableWithCustomCharacters(): void
    {
        $headers = ['foo', 'bar'];
        $rows = [
            ['a'],
            ['aa', 'bb'],
            ['aaa', 'bbb', 'ccc']
        ];
        $options = new TableFormatterOptions(
            cellPaddingString: '_',
            verticalBorderChar: 'I',
            horizontalBorderChar: '=',
            intersectionChar: '*',
            padAfter: false,
            eolChar: '<br>'
        );
        $expected =
            '*=====*=====*=====*<br>' .
            'I_foo_I_bar_I_____I<br>' .
            '*=====*=====*=====*<br>' .
            'I___a_I_____I_____I<br>' .
            'I__aa_I__bb_I_____I<br>' .
            'I_aaa_I_bbb_I_ccc_I<br>' .
            '*=====*=====*=====*';
        $this->assertSame($expected, $this->formatter->format($rows, $headers, $options));
    }

    public function testFormattingTableWithCustomPaddingString(): void
    {
        $rows = [['a']];
        $options = new TableFormatterOptions(cellPaddingString: '__');
        $expected =
            '+-----+' . PHP_EOL .
            '|__a__|' . PHP_EOL .
            '+-----+';
        $this->assertSame($expected, $this->formatter->format($rows, options: $options));
    }

    public function testFormattingTableWithHeadersButWithoutRows(): void
    {
        $this->assertEmpty($this->formatter->format([], ['foo', 'bar']));
    }

    public function testFormattingTableWithMoreHeadersThanRowColumns(): void
    {
        $headers = ['foo', 'bar', 'baz', 'blah'];
        $rows = [
            ['a'],
            ['aa', 'bb'],
            ['aaa', 'bbb', 'ccc']
        ];
        $expected =
            '+-----+-----+-----+------+' . PHP_EOL .
            '| foo | bar | baz | blah |' . PHP_EOL .
            '+-----+-----+-----+------+' . PHP_EOL .
            '| a   |     |     |      |' . PHP_EOL .
            '| aa  | bb  |     |      |' . PHP_EOL .
            '| aaa | bbb | ccc |      |' . PHP_EOL .
            '+-----+-----+-----+------+';
        $this->assertSame($expected, $this->formatter->format($rows, $headers));
    }

    public function testFormattingTableWithMoreRowColumnsThanHeaders(): void
    {
        $headers = ['foo', 'bar'];
        $rows = [
            ['a'],
            ['aa', 'bb'],
            ['aaa', 'bbb', 'ccc']
        ];
        $expected =
            '+-----+-----+-----+' . PHP_EOL .
            '| foo | bar |     |' . PHP_EOL .
            '+-----+-----+-----+' . PHP_EOL .
            '| a   |     |     |' . PHP_EOL .
            '| aa  | bb  |     |' . PHP_EOL .
            '| aaa | bbb | ccc |' . PHP_EOL .
            '+-----+-----+-----+';
        $this->assertSame($expected, $this->formatter->format($rows, $headers));
    }

    public function testFormattingTableWithoutHeaders(): void
    {
        $rows = [
            ['a'],
            ['aa', 'bb'],
            ['aaa', 'bbb', 'ccc']
        ];
        $expected =
            '+-----+-----+-----+' . PHP_EOL .
            '| a   |     |     |' . PHP_EOL .
            '| aa  | bb  |     |' . PHP_EOL .
            '| aaa | bbb | ccc |' . PHP_EOL .
            '+-----+-----+-----+';
        $this->assertSame($expected, $this->formatter->format($rows));
    }

    public function testSettingRowsWithNonArrayValues(): void
    {
        $expected =
            '+-----+' . PHP_EOL .
            '| foo |' . PHP_EOL .
            '| bar |' . PHP_EOL .
            '+-----+';
        $this->assertSame($expected, $this->formatter->format(['foo', 'bar']));
    }
}
