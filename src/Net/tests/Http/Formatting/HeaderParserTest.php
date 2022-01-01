<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Net\Http\Formatting\HeaderParser;
use Aphiria\Net\Http\Headers;
use PHPUnit\Framework\TestCase;

class HeaderParserTest extends TestCase
{
    private HeaderParser $parser;

    protected function setUp(): void
    {
        $this->parser = new HeaderParser();
    }

    public function testCheckingIfJsonChecksContentTypeHeader(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isJson($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->parser->isJson($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json; charset=utf-8');
        $this->assertTrue($this->parser->isJson($headers));
    }

    public function testCheckingIfMultipartChecksContentTypeHeader(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'text/plain');
        $this->assertFalse($this->parser->isMultipart($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/mixed');
        $this->assertTrue($this->parser->isMultipart($headers));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/form-data');
        $this->assertTrue($this->parser->isMultipart($headers));
    }

    public function testCheckingIfMultipartReturnsFalseIfNoContentTypeHeaderIsSpecified(): void
    {
        $headers = new Headers();
        $this->assertFalse($this->parser->isMultipart($headers));
    }

    public function testParseContentTypeHeaderReturnsIt(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'application/json');
        $value = $this->parser->parseContentTypeHeader($headers);
        $this->assertSame('application/json', $value?->mediaType);
    }

    public function testParsingParametersForIndexThatDoesNotExistReturnsEmptyDictionary(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar; baz');
        $this->assertEquals(new ImmutableHashTable([]), $this->parser->parseParameters($headers, 'Foo', 1));
    }

    public function testParsingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar; baz="blah"');
        $values = $this->parser->parseParameters($headers, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertSame('blah', $values->get('baz'));
    }

    public function testParsingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar=baz');
        $headers->add('Bar', 'bar="baz"');
        $this->assertSame('baz', $this->parser->parseParameters($headers, 'Foo')->get('bar'));
        $this->assertSame('baz', $this->parser->parseParameters($headers, 'Bar')->get('bar'));
    }

    public function testIsJsonForHeadersWithoutContentTypeReturnsFalse(): void
    {
        $this->assertFalse($this->parser->isJson(new Headers()));
    }
}
