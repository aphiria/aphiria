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

use Aphiria\Console\Output\Formatters\TableFormatterOptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TableFormatterOptionsTest extends TestCase
{
    public function testEmptyEolCharThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EOL character cannot be empty');
        /** @psalm-suppress InvalidArgument Purposely testing an invalid character */
        new TableFormatterOptions(eolChar: '');
    }

    public function testPropertiesAreSetInConstructor(): void
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
