<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\SnakeCasePropertyNameFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the snake case property name formatter
 */
class SnakeCasePropertyNameFormatterTest extends TestCase
{
    /** @var SnakeCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
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
