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

use Aphiria\Console\Output\Formatters\TableFormatterOptions;
use PHPUnit\Framework\TestCase;

class TableFormatterOptionsTest extends TestCase
{
    public function testProptiesAreSetInConstructor(): void
    {
        $options = new TableFormatterOptions(
            'padding',
            'verticalBorder',
            'horizontalBorder',
            'intersection',
            false,
            "\r"
        );
        $this->assertSame('padding', $options->cellPaddingString);
        $this->assertSame('verticalBorder', $options->verticalBorderChar);
        $this->assertSame('horizontalBorder', $options->horizontalBorderChar);
        $this->assertSame('intersection', $options->intersectionChar);
        $this->assertFalse($options->padAfter);
        $this->assertSame("\r", $options->eolChar);
    }
}
