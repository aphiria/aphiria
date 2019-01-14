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
use PHPUnit\Framework\TestCase;

/**
 * Tests the camel case property name formatter
 */
class CamelCasePropertyNameFormatterTest extends TestCase
{
    /** @var CamelCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new CamelCasePropertyNameFormatter();
    }

    public function propertyNamesAreCamelCasedProvider(): array
    {
        return [
            ['foo_bar', 'fooBar'],
            ['bar-baz', 'barBaz'],
            ['baz blah', 'bazBlah'],
            ['blahDave', 'blahDave'],
        ];
    }

    /**
     * @dataProvider propertyNamesAreCamelCasedProvider
     */
    public function testPropertyNamesAreCamelCased($propertyName, $expectedFormattedPropertyName): void
    {
        $this->assertEquals(
            $expectedFormattedPropertyName,
            $this->formatter->formatPropertyName($propertyName)
        );
    }
}
