<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\SnakeCasePropertyNameFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the snake case property name formatter
 */
class SnakeCasePropertyNameFormatterTest extends TestCase
{
    private SnakeCasePropertyNameFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new SnakeCasePropertyNameFormatter();
    }

    public function propertyNamesAreSnakeCasedProvider(): array
    {
        return [
            ['foo_bar', 'foo_bar'],
            ['bar-baz', 'bar_baz'],
            ['baz blah', 'baz_blah'],
            ['blahDave', 'blah_dave'],
        ];
    }

    /**
     * @dataProvider propertyNamesAreSnakeCasedProvider
     */
    public function testPropertyNamesAreSnakeCased($propertyName, $expectedFormattedPropertyName): void
    {
        $this->assertEquals(
            $expectedFormattedPropertyName,
            $this->formatter->formatPropertyName($propertyName)
        );
    }
}
