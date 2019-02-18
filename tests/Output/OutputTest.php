<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\Compilers\OutputCompiler;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\OutputLexer;
use Aphiria\Console\Output\Compilers\Parsers\OutputParser;
use Aphiria\Console\Tests\Output\Mocks\Output;
use PHPUnit\Framework\TestCase;

/**
 * Tests the output class
 */
class OutputTest extends TestCase
{
    /** @var Output The output to use in tests */
    private $output;

    public function setUp(): void
    {
        $this->output = new Output(new OutputCompiler(new OutputLexer(), new OutputParser()));
    }

    public function testClearingOutput(): void
    {
        ob_start();
        $this->output->clear();
        $this->assertEquals(chr(27) . '[2J' . chr(27) . '[;H', ob_get_clean());
    }

    public function testWritingMultipleMessagesWithNewLines(): void
    {
        ob_start();
        $this->output->writeln(['foo', 'bar']);
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, ob_get_clean());
    }

    public function testWritingMultipleMessagesWithNoNewLines(): void
    {
        ob_start();
        $this->output->write(['foo', 'bar']);
        $this->assertEquals('foobar', ob_get_clean());
    }

    public function testWritingSingleMessageWithNewLine(): void
    {
        ob_start();
        $this->output->writeln('foo');
        $this->assertEquals('foo' . PHP_EOL, ob_get_clean());
    }

    public function testWritingSingleMessageWithNoNewLine(): void
    {
        ob_start();
        $this->output->write('foo');
        $this->assertEquals('foo', ob_get_clean());
    }

    public function testWritingStyledMessageWithStylingDisabled(): void
    {
        ob_start();
        $this->output->setStyled(false);
        $this->output->write('<b>foo</b>');
        $this->assertEquals('foo', ob_get_clean());
    }
}
