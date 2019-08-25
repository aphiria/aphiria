<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\CamelCasePropertyNameFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the camel case property name formatter
 */
class CamelCasePropertyNameFormatterTest extends TestCase
{
    private CamelCasePropertyNameFormatter $formatter;

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
