<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\SnakeCasePropertyNameFormatter;

/**
 * Tests the snake_case property name formatter
 */
class SnakeCasePropertyNameFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var SnakeCasePropertyNameFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new SnakeCasePropertyNameFormatter();
    }

    public function testDecodingAnyValueJustReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onDecoding('foo', 'string'));
        $this->assertEquals(['foo-bar' => 'baz'], $this->formatter->onDecoding(['foo-bar' => 'baz'], 'array'));
    }

    public function testEncodingArrayWithNumericKeysLeavesNumericKeys(): void
    {
        $expectedEncodedValue = ['foo', 'bar'];
        $this->assertEquals($expectedEncodedValue, $this->formatter->onEncoding(['foo', 'bar'], 'array'));
    }

    public function testEncodingAssociativeArrayConvertsKeysToSnakeCase(): void
    {
        $value = ['foo_bar' => 'foo', 'bar-baz' => 'bar', 'baz blah' => 'baz', 'blahDave' => 'blah'];
        $expectedEncodedValue = ['foo_bar' => 'foo', 'bar_baz' => 'bar', 'baz_blah' => 'baz', 'blah_dave' => 'blah'];
        $this->assertEquals($expectedEncodedValue, $this->formatter->onEncoding($value, 'array'));
    }

    public function testEncodingNonArraysReturnsValue(): void
    {
        $this->assertEquals('foo', $this->formatter->onEncoding('foo', 'string'));
    }
}
