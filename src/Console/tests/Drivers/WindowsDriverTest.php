<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Drivers;

use Aphiria\Console\Drivers\WindowsDriver;
use Aphiria\Console\StatusCode;
use PHPUnit\Framework\TestCase;

class WindowsDriverTest extends TestCase
{
    private string|bool $ansicon;
    private string|bool $columns;
    private WindowsDriver $driver;
    private string|bool $lines;

    protected function setUp(): void
    {
        $this->driver = new WindowsDriver();
        $this->ansicon = \getenv('ANSICON');
        $this->columns = \getenv('COLUMNS');
        $this->lines = \getenv('LINES');
    }

    protected function tearDown(): void
    {
        if ($this->ansicon !== false) {
            \putenv("ANSICON={$this->ansicon}");
        }

        if ($this->columns !== false) {
            \putenv("COLUMNS={$this->columns}");
        }

        if ($this->lines !== false) {
            \putenv("LINES={$this->lines}");
        }
    }

    public function testCliDimensionsAlwaysHaveFallbackDimensions(): void
    {
        \putenv('COLUMNS');
        \putenv('LINES');
        \putenv('ANSICON');
        $driver = new class () extends WindowsDriver {
            protected function getCliDimensionsFromOS(): ?array
            {
                return null;
            }
        };
        $this->assertSame(60, $driver->getCliHeight());
        $this->assertSame(80, $driver->getCliWidth());
    }

    public function testCliDimensionsCanBeReadFromAnsicon(): void
    {
        \putenv('COLUMNS');
        \putenv('LINES');
        \putenv('ANSICON=10x15');
        $this->assertSame(10, $this->driver->getCliWidth());
        $this->assertSame(15, $this->driver->getCliHeight());
    }

    public function testCliDimensionsCanBeReadFromOS(): void
    {
        \putenv('COLUMNS');
        \putenv('LINES');
        \putenv('ANSICON');
        $driver = new class () extends WindowsDriver {
            protected function getCliDimensionsFromOS(): ?array
            {
                return [10, 15];
            }
        };
        $this->assertSame(10, $driver->getCliWidth());
        $this->assertSame(15, $driver->getCliHeight());
    }

    public function testCliDimensionsCanBeReadFromSttyIfEnabled(): void
    {
        if (\DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped('This test can only be run on windows');
        }

        $sttyOutput = \exec('(stty -a | grep columns) 2>&1', $output, $statusCode);

        if ($statusCode !== StatusCode::Ok->value) {
            $this->markTestSkipped('This test can only be run on Windows with STTY support');
        }

        $matches = [];

        if (
            \preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyOutput, $matches) !== 1
            && \preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyOutput, $matches) !== 1
        ) {
            $this->fail('Dimensions could not be read from STTY output');
        }

        $this->assertSame((int)$matches[2], $this->driver->getCliWidth());
        $this->assertSame((int)$matches[1], $this->driver->getCliHeight());
    }

    public function testCliHeightIsMemoized(): void
    {
        \putenv('LINES=10');
        $this->assertSame(10, $this->driver->getCliHeight());
        \putenv('LINES=0');
        $this->assertSame(10, $this->driver->getCliHeight());
    }

    public function testCliWidthIsMemoized(): void
    {
        \putenv('COLUMNS=10');
        $this->assertSame(10, $this->driver->getCliWidth());
        \putenv('COLUMNS=0');
        $this->assertSame(10, $this->driver->getCliWidth());
    }
}
