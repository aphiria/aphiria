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

use Aphiria\Console\Output\Formatters\IProgressBarObserver;
use Aphiria\Console\Output\Formatters\ProgressBar;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the progress bar
 */
class ProgressBarTest extends TestCase
{
    private ProgressBar $progressBar;
    /** @var IProgressBarObserver|MockObject */
    private IProgressBarObserver $formatter;

    protected function setUp(): void
    {
        $this->formatter = $this->createMock(IProgressBarObserver::class);
        $this->progressBar = new ProgressBar(100, $this->formatter);
    }

    public function testAdvancingBeyondMaxStepsDoesNotCallFormatter(): void
    {
        $this->formatter->expects($this->once())
            ->method('onProgressChanged')
            ->with(0, 100, 100);
        $this->progressBar->finish();
        $this->progressBar->advance();
    }

    public function testConstructingWithNegativeMaxStepsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max steps must be greater than 0');
        new ProgressBar(-1, $this->formatter);
    }

    public function testFinishingTwiceOnlyCallsFormatterOnce(): void
    {
        $this->formatter->expects($this->once())
            ->method('onProgressChanged')
            ->with(0, 100, 100);
        $this->progressBar->finish();
        $this->progressBar->finish();
    }

    public function testIsCompleteOnlyReturnsTrueIfWeReachedMaxSteps(): void
    {
        $this->assertFalse($this->progressBar->isComplete());
        $this->progressBar->advance();
        $this->assertFalse($this->progressBar->isComplete());
        $this->progressBar->finish();
        $this->assertTrue($this->progressBar->isComplete());
    }

    public function testSettingProgressToValueLessThanZeroBoundsItToZero(): void
    {
        $this->formatter->expects($this->at(0))
            ->method('onProgressChanged')
            ->with(0, 1, 100);
        $this->formatter->expects($this->at(1))
            ->method('onProgressChanged')
            ->with(1, 0, 100);
        // Note: We're advancing at least once so that the update is sent to the formatter
        $this->progressBar->advance();
        $this->progressBar->setProgress(-1);
    }

    public function testSettingProgressToValueOverMaxStepsBoundsItToMaxSteps(): void
    {
        $this->formatter->expects($this->once())
            ->method('onProgressChanged')
            ->with(0, 100, 100);
        $this->progressBar->setProgress(500);
    }

    public function testSettingProgressToZeroStillNotifiesObserversOfProgress(): void
    {
        $this->formatter->expects($this->at(0))
            ->method('onProgressChanged')
            ->with(0, 0, 100);
        $this->progressBar->setProgress(0);
    }
}
