<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\Encoding\CamelCasePropertyNameFormatter;

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

    public function testDecodingAnyValueJustReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onPreDecoding('foo', 'string'));
        $this->assertEquals(['foo_bar' => 'baz'], $this->formatter->onPreDecoding(['foo_bar' => 'baz'], 'array'));
    }

    public function testEncodingArrayWithNumericKeysLeavesNumericKeys(): void
    {
        $expectedEncodedValue = ['foo', 'bar'];
        $this->assertEquals($expectedEncodedValue, $this->formatter->onPostEncoding(['foo', 'bar'], 'array'));
    }

    public function testEncodingAssociativeArrayConvertsKeysToCamelCase(): void
    {
        $value = ['foo_bar' => 'foo', 'bar-baz' => 'bar', 'baz blah' => 'baz', 'blahDave' => 'blah'];
        $expectedEncodedValue = ['fooBar' => 'foo', 'barBaz' => 'bar', 'bazBlah' => 'baz', 'blahDave' => 'blah'];
        $this->assertEquals($expectedEncodedValue, $this->formatter->onPostEncoding($value, 'array'));
    }

    public function testEncodingNonArraysReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onPostEncoding('foo', 'string'));
    }
}
