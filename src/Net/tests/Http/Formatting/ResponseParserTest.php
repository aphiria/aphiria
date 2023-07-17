<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Formatting;

use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Formatting\ResponseParser;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\Headers\SameSiteMode;
use Aphiria\Net\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    private ResponseParser $responseParser;

    protected function setUp(): void
    {
        $this->responseParser = new ResponseParser();
    }

    public function testCheckingIfJsonChecksContentTypeHeader(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'text/plain');
        $response = new Response(headers: $headers);
        $this->assertFalse($this->responseParser->isJson($response));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json');
        $this->assertTrue($this->responseParser->isJson($response));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'application/json; charset=utf-8');
        $this->assertTrue($this->responseParser->isJson($response));
    }

    public function testCheckingIfMultipartChecksContentTypeHeader(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'text/plain');
        $response = new Response(headers: $headers);
        $this->assertFalse($this->responseParser->isMultipart($response));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/mixed');
        $this->assertTrue($this->responseParser->isMultipart($response));
        $headers->removeKey('Content-Type');
        $headers->add('Content-Type', 'multipart/form-data');
        $this->assertTrue($this->responseParser->isMultipart($response));
    }

    public function testCheckingIfMultipartReturnsFalseIfNoContentTypeHeaderIsSpecified(): void
    {
        $headers = new Headers();
        $response = new Response(headers: $headers);
        $this->assertFalse($this->responseParser->isMultipart($response));
    }

    public function testIsJsonForHeadersWithoutContentTypeReturnsFalse(): void
    {
        $response = new Response(headers: new Headers());
        $this->assertFalse($this->responseParser->isJson($response));
    }

    public function testParseContentTypeHeaderReturnsIt(): void
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'application/json');
        $response = new Response(headers: $headers);
        $value = $this->responseParser->parseContentTypeHeader($response);
        $this->assertSame('application/json', $value?->mediaType);
    }

    public function testParsingCookiesParsesAllAvailableParametersIntoCookies(): void
    {
        $headers = new Headers([
            new KeyValuePair('Set-Cookie', 'foo=value; Max-Age=3600; Domain=example.com; Path=/path; HttpOnly; Secure; SameSite=strict')
        ]);
        $response = new Response(headers: $headers);
        $expectedCookies = new ImmutableHashTable([
            new KeyValuePair(
                'foo',
                new Cookie('foo', 'value', 3600, '/path', 'example.com', true, true, SameSiteMode::Strict)
            )
        ]);
        $this->assertEquals($expectedCookies, $this->responseParser->parseCookies($response));
    }

    public function testParsingCookiesParsesMultipleCookies(): void
    {
        $headers = new Headers();
        $headers->add('Set-Cookie', 'foo=value1; Max-Age=3600; Domain=example1.com; Path=/path1; HttpOnly; Secure; SameSite=strict');
        $headers->add('Set-Cookie', 'bar=value2; Max-Age=7200; Domain=example2.com; Path=/path2; HttpOnly; Secure; SameSite=strict', true);
        $response = new Response(headers: $headers);
        $expectedCookies = new ImmutableHashTable([
            new KeyValuePair(
                'foo',
                new Cookie('foo', 'value1', 3600, '/path1', 'example1.com', true, true, SameSiteMode::Strict)
            ),
            new KeyValuePair(
                'bar',
                new Cookie('bar', 'value2', 7200, '/path2', 'example2.com', true, true, SameSiteMode::Strict)
            )
        ]);
        $this->assertEquals($expectedCookies, $this->responseParser->parseCookies($response));
    }

    public function testParsingCookiesWithoutAnySetReturnsEmptyList(): void
    {
        $response = new Response(headers: new Headers());
        $this->assertEmpty($this->responseParser->parseCookies($response));
    }

    public function testParsingCookiesWithoutHttpOnlyFlagSetsFlagToFalseInCookies(): void
    {
        $headers = new Headers();
        $headers->add('Set-Cookie', 'foo=bar; Max-Age=3600');
        $response = new Response(headers: $headers);
        $expectedCookies = new ImmutableHashTable([
            new KeyValuePair(
                'foo',
                new Cookie('foo', 'bar', 3600, null, null, false, false, null)
            )
        ]);
        $this->assertEquals($expectedCookies, $this->responseParser->parseCookies($response));
    }

    public function testParsingCookiesWithoutNameDoesNotIncludeThem(): void
    {
        $headers = new Headers();
        $headers->add('Set-Cookie', '');
        $response = new Response(headers: $headers);
        $this->assertEmpty($this->responseParser->parseCookies($response));
    }

    public function testParsingCookiesWithoutSecureFlagSetsFlagToFalseInCookies(): void
    {
        $headers = new Headers();
        $headers->add('Set-Cookie', 'foo=bar; Max-Age=3600');
        $response = new Response(headers: $headers);
        $expectedCookies = new ImmutableHashTable([
            new KeyValuePair(
                'foo',
                new Cookie('foo', 'bar', 3600, null, null, false, false, null)
            )
        ]);
        $this->assertEquals($expectedCookies, $this->responseParser->parseCookies($response));
    }

    public function testParsingParametersForIndexThatDoesNotExistReturnsEmptyDictionary(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar; baz');
        $response = new Response(headers: $headers);
        $this->assertEquals(new ImmutableHashTable([]), $this->responseParser->parseParameters($response, 'Foo', 1));
    }

    public function testParsingParametersWithMixOfValueAndValueLessParametersReturnsCorrectParameters(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar; baz="blah"');
        $response = new Response(headers: $headers);
        $values = $this->responseParser->parseParameters($response, 'Foo');
        $this->assertNull($values->get('bar'));
        $this->assertSame('blah', $values->get('baz'));
    }

    public function testParsingParametersWithQuotedAndUnquotedValuesReturnsArrayWithUnquotedValue(): void
    {
        $headers = new Headers();
        $headers->add('Foo', 'bar=baz');
        $headers->add('Bar', 'bar="baz"');
        $response = new Response(headers: $headers);
        $this->assertSame('baz', $this->responseParser->parseParameters($response, 'Foo')->get('bar'));
        $this->assertSame('baz', $this->responseParser->parseParameters($response, 'Bar')->get('bar'));
    }
}
