<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\CamelCasePropertyNameFormatter;

/**
 * Tests the camel case property name formatter
 */
class CamelCasePropertyNameFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var CamelCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new CamelCasePropertyNameFormatter();
    }

    public function testPropertyNamesAreCamelCased(): void
    {
        $propertyNames = ['foo_bar', 'bar-baz', 'baz blah', 'blahDave'];
        $expectedFormattedPropertyNames = ['fooBar', 'barBaz', 'bazBlah', 'blahDave'];

        for ($i = 0;$i < count($propertyNames);$i++) {
            $this->assertEquals(
                $expectedFormattedPropertyNames[$i],
                $this->formatter->formatPropertyName($propertyNames[$i])
            );
        }
    }
}
