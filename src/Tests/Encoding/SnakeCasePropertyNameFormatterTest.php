<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\SnakeCasePropertyNameFormatter;

/**
 * Tests the snake case property name formatter
 */
class SnakeCasePropertyNameFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var CamelCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new SnakeCasePropertyNameFormatter();
    }

    public function testPropertyNamesAreSnakeCased(): void
    {
        $propertyNames = ['foo_bar', 'bar-baz', 'baz blah', 'blahDave'];
        $expectedFormattedPropertyNames = ['foo_bar', 'bar_baz', 'baz_blah', 'blah_dave'];

        for ($i = 0;$i < count($propertyNames);$i++) {
            $this->assertEquals(
                $expectedFormattedPropertyNames[$i],
                $this->formatter->formatPropertyName($propertyNames[$i])
            );
        }
    }
}
