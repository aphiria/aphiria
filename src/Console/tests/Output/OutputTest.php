<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Output\Compilers\IOutputCompiler;
use Aphiria\Console\Tests\Output\Mocks\Output;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    private Output $output;

    protected function setUp(): void
    {
        $this->output = new Output();
    }

    public function testClearingOutput(): void
    {
        ob_start();
        $this->output->clear();
        $this->assertSame(\chr(27) . '[2J' . \chr(27) . '[;H', ob_get_clean());
    }

    public function testGetDriverReturnsOneSetInConstructor(): void
    {
        $driver = $this->createMock(IDriver::class);
        $output = new Output($this->createMock(IOutputCompiler::class), $driver);
        $this->assertSame($driver, $output->getDriver());
    }

    public function testWritingMultipleMessagesWithNewLines(): void
    {
        ob_start();
        $this->output->writeln(['foo', 'bar']);
        $this->assertSame('foo' . PHP_EOL . 'bar' . PHP_EOL, ob_get_clean());
    }

    public function testWritingMultipleMessagesWithNoNewLines(): void
    {
        ob_start();
        $this->output->write(['foo', 'bar']);
        $this->assertSame('foobar', ob_get_clean());
    }

    public function testWritingSingleMessageWithNewLine(): void
    {
        ob_start();
        $this->output->writeln('foo');
        $this->assertSame('foo' . PHP_EOL, ob_get_clean());
    }

    public function testWritingSingleMessageWithNoNewLine(): void
    {
        ob_start();
        $this->output->write('foo');
        $this->assertSame('foo', ob_get_clean());
    }

    public function testWritingStyledMessageWithStylingDisabled(): void
    {
        ob_start();
        $this->output->includeStyles(false);
        $this->output->write('<b>foo</b>');
        $this->assertSame('foo', ob_get_clean());
    }
}
