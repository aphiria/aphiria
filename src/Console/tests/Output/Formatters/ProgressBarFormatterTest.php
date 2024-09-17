<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Output\Formatters\ProgressBarFormatter;
use Aphiria\Console\Output\Formatters\ProgressBarFormatterOptions;
use Aphiria\Console\Output\IOutput;
use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class ProgressBarFormatterTest extends TestCase
{
    private IOutput&MockObject $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
        $driver = new class () implements IDriver {
            public int $cliWidth = 3;
            public int $cliHeight = 2;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $this->output->method(PropertyHook::get('driver'))
            ->willReturn($driver);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public static function baseCaseProvider(): array
    {
        return [
            [1, '[1%--------]'],
            [9, '[9%--------]'],
            [10, '[10%-------]'],
            [19, '[19%-------]'],
            [20, '[20%-------]'],
            [29, '[29%-------]'],
            [30, '[30%-------]'],
            [39, '[39%-------]'],
            [40, '[=40%------]'],
            [49, '[=49%------]'],
            [50, '[==50%-----]'],
            [59, '[==59%-----]'],
            [60, '[===60%----]'],
            [69, '[===69%----]'],
            [70, '[====70%---]'],
            [79, '[====79%---]'],
            [80, '[=====80%--]'],
            [89, '[=====89%--]'],
            [90, '[======90%-]'],
            [99, '[======99%-]'],
        ];
    }

    public function testOnProgressClearsPreviousOutputUsingAnsiCodes(): void
    {
        $this->markTestSkipped('Waiting until https://github.com/mockery/mockery/issues/1438 is implemented');
        // Use a redraw frequency of 0 so that it redraws every time
        $output = Mockery::mock(MockableOutput::class);
        $driver = new class () implements IDriver {
            public int $cliWidth = 3;
            public int $cliHeight = 2;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $output->driver = $driver;
        $output->shouldReceive('write')
            ->withAnyArgs();
        $output->shouldReceive('write')
            ->with(
                fn (mixed $value): bool => $this->progressBarMatchesExpectedValue("\033[2K\033[0G\033[1A\033[2K[20%-------] 2/10" . \PHP_EOL . 'Time remaining:', $value, true)
            );
        $formatter = new ProgressBarFormatter($output);
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12, redrawFrequency: 0);
        $formatter->onProgressChanged(0, 1, 10, $options);
        $formatter->onProgressChanged(1, 2, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testOnProgressThatReachesMaxStepsDrawsCompleteProgressBar(): void
    {
        $formatter = new ProgressBarFormatter($this->output);
        $this->output->method('write')
            ->with($this->callback(fn (mixed $value): bool => $this->progressBarMatchesExpectedValue('[==========] 10/10' . \PHP_EOL . 'Time remaining: Complete', $value, false)));
        $this->output->method('writeln')
            ->with('');
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12);
        $formatter->onProgressChanged(0, 10, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testOnProgressWithFormatThatIncludesPercentPopulatesPercent(): void
    {
        $formatter = new ProgressBarFormatter($this->output);
        $this->output->method('write')
            ->with('50%');
        $options = new ProgressBarFormatterOptions(progressBarWidth: 10, outputFormat: '%percent%');
        $formatter->onProgressChanged(0, 5, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testOnProgressWithImpossiblyLowTimeLeftShowsCorrectTime(): void
    {
        $formatter = new class ($this->output) extends ProgressBarFormatter {
            protected function getSecondsRemaining(int $progress, int $maxSteps): float
            {
                return -1;
            }
        };
        $this->output->method('write')
            ->with($this->callback(fn (mixed $value): bool => $this->progressBarMatchesExpectedValue('[10%-------] 1/10' . \PHP_EOL . 'Time remaining: Estimating...', $value, false)));
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12);
        $formatter->onProgressChanged(0, 1, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testOnProgressWithVeryLongTimeLeftShowsCorrectTime(): void
    {
        $formatter = new class ($this->output) extends ProgressBarFormatter {
            protected function getSecondsRemaining(int $progress, int $maxSteps): float
            {
                return 172800;
            }
        };
        $this->output->method('write')
            ->with($this->callback(fn (mixed $value): bool => $this->progressBarMatchesExpectedValue('[10%-------] 1/10' . \PHP_EOL . 'Time remaining: 2 days', $value, false)));
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12);
        $formatter->onProgressChanged(0, 1, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testOnProgressWithZeroProgressIndicatesThatTheTimeRemainingIsStillBeingEstimated(): void
    {
        $formatter = new ProgressBarFormatter($this->output);
        $this->output->method('write')
            ->with($this->callback(fn (mixed $value): bool => $this->progressBarMatchesExpectedValue('[0%--------] 0/10' . \PHP_EOL . 'Time remaining: Estimating...', $value, false)));
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12);
        $formatter->onProgressChanged(null, 0, 10, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * @param int $percentComplete The percent completed
     * @param string $expectedString The expected string
     * @throws Exception Thrown on error
     */
    #[DataProvider('baseCaseProvider')]
    public function testOnProgressWritesCorrectStringsForBaseCases(int $percentComplete, string $expectedString): void
    {
        $formatter = new ProgressBarFormatter($this->output);
        $this->output->method('write')
            ->with($this->callback(fn (mixed $value): bool => $this->progressBarMatchesExpectedValue($expectedString . " $percentComplete/100" . \PHP_EOL . 'Time remaining:', $value, true)));
        $options = new ProgressBarFormatterOptions(progressBarWidth: 12);
        $formatter->onProgressChanged(0, $percentComplete, 100, $options);
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Gets whether or not the progress bar output matches the expected output
     *
     * @param string $expectedValue The expected Value
     * @param string $actualValue The actual value
     * @param bool $ignoreTimeRemaining Whether or not we want to take into consideration the time remaining
     * @return bool True if the expected value matches the actual one, otherwise false
     */
    private function progressBarMatchesExpectedValue(string $expectedValue, string $actualValue, bool $ignoreTimeRemaining): bool
    {
        if ($ignoreTimeRemaining) {
            return \str_starts_with($actualValue, $expectedValue);
        }

        return $actualValue === $expectedValue;
    }
}
