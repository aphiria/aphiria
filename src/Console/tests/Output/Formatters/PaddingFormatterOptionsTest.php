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

use Aphiria\Console\Output\Formatters\PaddingFormatterOptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PaddingFormatterOptionsTest extends TestCase
{
    public function testEmptyEolCharThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EOL character cannot be empty');
        /** @psalm-suppress InvalidArgument Purposely testing an invalid character */
        new PaddingFormatterOptions(eolChar: '');
    }

    public function testPropertiesSetInConstructor(): void
    {
        $options = new PaddingFormatterOptions(
            paddingString: '_',
            padAfter: false,
            eolChar: '<br>'
        );
        $this->assertSame('_', $options->paddingString);
        $this->assertFalse($options->padAfter);
        $this->assertSame('<br>', $options->eolChar);
    }
}
