<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\CamelCasePropertyNameFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the camel case property name formatter
 */
class CamelCasePropertyNameFormatterTest extends TestCase
{
    /** @var CamelCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    protected function setUp(): void
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
