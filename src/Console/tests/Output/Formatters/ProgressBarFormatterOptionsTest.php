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

use Aphiria\Console\Output\Formatters\ProgressBarFormatterOptions;
use PHPUnit\Framework\TestCase;

class ProgressBarFormatterOptionsTest extends TestCase
{
    public function testPropertiesSetInConstructor(): void
    {
        $options = new ProgressBarFormatterOptions(
            progressBarWidth: 100,
            outputFormat: '%bar%',
            completedProgressChar: '+',
            remainingProgressChar: '=',
            redrawFrequency: 2
        );
        $this->assertSame(100, $options->progressBarWidth);
        $this->assertSame('%bar%', $options->outputFormat);
        $this->assertSame('+', $options->completedProgressChar);
        $this->assertSame('=', $options->remainingProgressChar);
        $this->assertSame(2, $options->redrawFrequency);
    }
}
