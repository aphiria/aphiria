<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Output\Formatters\ProgressBarFormatter;
use Aphiria\Console\Output\IOutput;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the progress bar formatter
 */
class ProgressBarFormatterTest extends TestCase
{
    /** @var IOutput|MockObject */
    private IOutput $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
    }

    public function baseCaseProvider(): array
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
        // Use a redraw frequency of 0 so that it redraws every time
        $formatter = new ProgressBarFormatter($this->output, 12, null, 0);
        $this->output->expects($this->at(1))
            ->method('write')
            ->with($this->callback(fn ($value) => $this->progressBarMatchesExpectedValue("\033[2K\033[0G\033[1A\033[2K[20%-------] 2/10" . \PHP_EOL . 'Time remaining:', $value, true)));
        $formatter->onProgressChanged(0, 1, 10);
        $formatter->onProgressChanged(1, 2, 10);
    }

    public function testOnProgressThatReachesMaxStepsDrawsCompleteProgressBar(): void
    {
        $formatter = new ProgressBarFormatter($this->output, 12);
        $this->output->expects($this->at(0))
            ->method('write')
            ->with($this->callback(fn ($value) => $this->progressBarMatchesExpectedValue('[==========] 10/10' . \PHP_EOL . 'Time remaining: Complete', $value, false)));
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('');
        $formatter->onProgressChanged(0, 10, 10);
    }

    public function testOnProgressWithFormatThatIncludesPercentPopulatesPercent(): void
    {
        $formatter = new ProgressBarFormatter($this->output, null, '%percent%');
        $this->output->expects($this->at(0))
            ->method('write')
            ->with('50%');
        $formatter->onProgressChanged(0, 5, 10);
    }

    public function testOnProgressWithZeroProgressIndicatesThatTheTimeRemainingIsStillBeingEstimated(): void
    {
        $formatter = new ProgressBarFormatter($this->output, 12);
        $this->output->expects($this->at(0))
            ->method('write')
            ->with($this->callback(fn ($value) => $this->progressBarMatchesExpectedValue('[0%--------] 0/10' . \PHP_EOL . 'Time remaining: Estimating...', $value, false)));
        $formatter->onProgressChanged(null, 0, 10);
    }

    /**
     * @dataProvider baseCaseProvider
     * @param int $percentComplete The percent completed
     * @param string $expectedString The expected string
     * @throws Exception Thrown on error
     */
    public function testOnProgressWritesCorrectStringsForBaseCases(int $percentComplete, string $expectedString): void
    {
        $formatter = new ProgressBarFormatter($this->output, 12);
        $this->output->expects($this->at(0))
            ->method('write')
            ->with($this->callback(fn ($value) => $this->progressBarMatchesExpectedValue($expectedString . " $percentComplete/100" . \PHP_EOL . 'Time remaining:', $value, true)));
        $formatter->onProgressChanged(0, $percentComplete, 100);
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
            return \strpos($actualValue, $expectedValue) === 0;
        }

        return $actualValue === $expectedValue;
    }
}
