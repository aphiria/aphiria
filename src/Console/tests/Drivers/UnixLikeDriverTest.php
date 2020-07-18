<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Drivers;

use Aphiria\Console\Drivers\HiddenInputNotSupportedException;
use Aphiria\Console\Drivers\UnixLikeDriver;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use PHPUnit\Framework\TestCase;

class UnixLikeDriverTest extends TestCase
{
    private UnixLikeDriver $driver;
    /** @var string|bool */
    private $ansicon;
    /** @var string|bool */
    private $columns;
    /** @var string|bool */
    private $lines;

    protected function setUp(): void
    {
        $this->driver = new UnixLikeDriver();
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

    public function testReadHiddenInputThrowsExceptionIfSttyIsNotSupported(): void
    {
        $this->expectException(HiddenInputNotSupportedException::class);
        $this->expectExceptionMessage('STTY must be supported to hide input');
        $driver = new class() extends UnixLikeDriver {
            protected function supportsStty(): bool
            {
                return false;
            }
        };
        $driver->readHiddenInput($this->createMock(IOutput::class));
    }

    public function testCliDimensionsAlwaysHaveFallbackDimensions(): void
    {
        \putenv('COLUMNS');
        \putenv('LINES');
        $driver = new class() extends UnixLikeDriver {
            protected function getCliDimensionsFromOS(): ?array
            {
                return null;
            }
        };
        $this->assertEquals(80, $driver->getCliWidth());
        $this->assertEquals(60, $driver->getCliHeight());
    }

    public function testCliDimensionsCanBeReadFromOS(): void
    {
        \putenv('COLUMNS');
        \putenv('LINES');
        \putenv('ANSICON');
        $driver = new class() extends UnixLikeDriver {
            protected function getCliDimensionsFromOS(): ?array
            {
                return [10, 15];
            }
        };
        $this->assertEquals(10, $driver->getCliWidth());
        $this->assertEquals(15, $driver->getCliHeight());
    }

    public function testCliDimensionsCanBeReadFromSttyIfEnabled(): void
    {
        $sttyOutput = \exec('(stty -a | grep columns) 2>&1', $output, $statusCode);

        if ($statusCode !== StatusCodes::OK) {
            $this->markTestSkipped('This test can only be run on *nix systems with STTY support');
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
        $this->assertEquals(10, $this->driver->getCliHeight());
        \putenv('LINES=0');
        $this->assertEquals(10, $this->driver->getCliHeight());
    }

    public function testCliWidthIsMemoized(): void
    {
        \putenv('COLUMNS=10');
        $this->assertEquals(10, $this->driver->getCliWidth());
        \putenv('COLUMNS=0');
        $this->assertEquals(10, $this->driver->getCliWidth());
    }
}
