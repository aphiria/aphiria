<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\SilentOutput;
use PHPUnit\Framework\TestCase;

class SilentOutputTest extends TestCase
{
    private SilentOutput $output;

    protected function setUp(): void
    {
        $this->output = new SilentOutput();
    }

    public function testClearDoesNothing(): void
    {
        $this->output->clear();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testReadLineReturnsEmptyString(): void
    {
        $this->assertSame('', $this->output->readLine());
    }

    public function testWrite(): void
    {
        ob_start();
        $this->output->write('foo');
        $this->assertEmpty(ob_get_clean());
    }

    public function testWriteln(): void
    {
        ob_start();
        $this->output->writeln('foo');
        $this->assertEmpty(ob_get_clean());
    }

    public function testWritingUsingUnderlyingMethodDoesNothing(): void
    {
        $output = new class() extends SilentOutput {
            public function doWrite(string $message, bool $includeNewLine): void
            {
                parent::doWrite($message, $includeNewLine);
            }
        };
        ob_start();
        $output->doWrite('foo', false);
        $this->assertEmpty(ob_get_clean());
    }
}
