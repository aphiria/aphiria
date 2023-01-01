<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Formatters;

use Aphiria\Console\Output\Formatters\IProgressBarObserver;
use Aphiria\Console\Output\Formatters\ProgressBar;
use Aphiria\Console\Output\Formatters\ProgressBarFormatterOptions;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProgressBarTest extends TestCase
{
    private ProgressBar $progressBar;
    private IProgressBarObserver&MockObject $formatter;

    protected function setUp(): void
    {
        $this->formatter = $this->createMock(IProgressBarObserver::class);
        $this->progressBar = new ProgressBar(100, $this->formatter);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAdvancingBeyondMaxStepsDoesNotCallFormatter(): void
    {
        $this->formatter->expects($this->once())
            ->method('onProgressChanged')
            ->with(0, 100, 100);
        $this->progressBar->complete();
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
        $this->progressBar->complete();
        $this->progressBar->complete();
    }

    public function testIsCompleteOnlyReturnsTrueIfWeReachedMaxSteps(): void
    {
        $this->assertFalse($this->progressBar->isComplete());
        $this->progressBar->advance();
        $this->assertFalse($this->progressBar->isComplete());
        $this->progressBar->complete();
        $this->assertTrue($this->progressBar->isComplete());
    }

    public function testSettingProgressToValueLessThanZeroBoundsItToZero(): void
    {
        $formatter = Mockery::mock(IProgressBarObserver::class);
        $options = new ProgressBarFormatterOptions();
        $formatter->shouldReceive('onProgressChanged')
            ->with(0, 1, 100, $options);
        $formatter->shouldReceive('onProgressChanged')
            ->with(1, 0, 100, $options);
        $progressBar = new ProgressBar(100, $formatter, $options);
        // Note: We're advancing at least once so that the update is sent to the formatter
        $progressBar->advance();
        $progressBar->setProgress(-1);
        // Dummy assertion
        $this->assertTrue(true);
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
        $this->formatter->method('onProgressChanged')
            ->with(0, 0, 100);
        $this->progressBar->setProgress(0);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
