<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Testing;

use Aphiria\Collections\KeyValuePair;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Framework\Api\Testing\AssertionFailedException;
use Aphiria\Framework\Api\Testing\ResponseAssertions;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

class ResponseAssertionsTest extends TestCase
{
    private ResponseAssertions $assertions;

    protected function setUp(): void
    {
        $this->assertions = new ResponseAssertions();
    }

    public function testAssertCookieEqualsDoesNotThrowOnMatch(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->assertions->assertCookieEquals('bar', $response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertCookieEqualsThrowsOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that cookie foo has expected value');
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->assertions->assertCookieEquals('baz', $response, 'foo');
    }

    public function testAssertCookieEqualsThrowsWhenCookieNotSet(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that cookie foo has expected value');
        $response = new Response(200);
        $this->assertions->assertCookieEquals('baz', $response, 'foo');
    }

    public function testAssertHasCookieDoesNotThrowWhenCookieExists(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->assertions->assertHasCookie($response, 'foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertHasCookieThrowWhenCookieDoesNotExist(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that cookie baz is set');
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->assertions->assertHasCookie($response, 'baz');
    }

    public function testAssertHasCookieThrowWhenNoCookiesAreSet(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that cookie foo is set');
        $response = new Response(200);
        $this->assertions->assertHasCookie($response, 'foo');
    }

    public function testAssertHasHeaderDoesNotThrowWhenHeaderExists(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->assertions->assertHasHeader($response, 'Foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertHasHeaderThrowWhenHeaderDoesNotExist(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that header Foo is set');
        $response = new Response(200);
        $this->assertions->assertHasHeader($response, 'Foo');
    }

    public function testAssertHeaderEqualsDoesNotThrowOnMatch(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->assertions->assertHeaderEquals(['bar'], $response, 'Foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertHeaderEqualsThrowsOnNoMatch(): void
    {
        $expectedValue = ['bar'];
        $actualHeaderValue = ['baz'];
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected header value ' . \print_r($expectedValue, true) . ', got ' . \print_r($actualHeaderValue, true));
        $response = new Response(200, new Headers([new KeyValuePair('Foo', $actualHeaderValue)]));
        $this->assertions->assertHeaderEquals($expectedValue, $response, 'Foo');
    }

    public function testAssertHeaderEqualsThrowsWhenHeaderIsNotSet(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('No header value for Foo is set');
        $response = new Response(200);
        $this->assertions->assertHeaderEquals(['bar'], $response, 'Foo');
    }

    public function testAssertHeaderMatchesRegexDoesNotThrowOnMatch(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', ['bar'])]));
        $this->assertions->assertHeaderMatchesRegex('/^bar$/', $response, 'Foo');
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertHeaderMatchesRegexThrowsOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('bar does not match regex /^baz$/');
        $response = new Response(200, new Headers([new KeyValuePair('Foo', ['bar'])]));
        $this->assertions->assertHeaderMatchesRegex('/^baz$/', $response, 'Foo');
    }

    public function testAssertHeaderMatchesRegexThrowsWhenHeaderIsNotSet(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('No header value for Foo is set');
        $response = new Response(200);
        $this->assertions->assertHeaderMatchesRegex('/^bar$/', $response, 'Foo');
    }

    public function testAssertParsedBodyEqualsWithBodyThatCannotBeNegotiatedThrows(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to parse the response body');
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{}');
        $response = new Response(200, null, $body);
        $mediaTypeFormatterMatcher = $this->createMock(IMediaTypeFormatterMatcher::class);
        $mediaTypeFormatterMatcher->expects($this->once())
            ->method('getBestResponseMediaTypeFormatterMatch')
            ->with(self::class, $request)
            ->willReturn(null);
        $assertions = new ResponseAssertions($mediaTypeFormatterMatcher);
        $assertions->assertParsedBodyEquals($this, $request, $response);
    }

    public function testAssertParsedBodyEqualsWithHttpBodyDoesNotThrowOnMatch(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $body = new StringBody('foo');
        $response = new Response(200, null, $body);
        $this->assertions->assertParsedBodyEquals($body, $request, $response);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertParsedBodyEqualsWithHttpBodyThrowOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that the response body equals the expected value');
        $request = new Request('GET', new Uri('http://localhost'));
        $body = new StringBody('foo');
        $response = new Response(200, null, $body);
        $this->assertions->assertParsedBodyEquals(new StringBody('baz'), $request, $response);
    }

    public function testAssertParsedBodyEqualsWithNonHttpBodyDoesNotThrowOnMatch(): void
    {
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{"foo":"bar"}');
        $response = new Response(200, null, $body);
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->assertions->assertParsedBodyEquals($expectedParsedBody, $request, $response);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertParsedBodyEqualsWithNonHttpBodyThrowOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that the response body matches the expected value');
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{"foo":"bar"}');
        $response = new Response(200, null, $body);
        $expectedParsedBody = new class() {
            public string $foo = 'baz';
        };
        $this->assertions->assertParsedBodyEquals($expectedParsedBody, $request, $response);
    }

    public function testAssertParsedBodyEqualsWithNullBodyThrows(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that the response body matches the expected value');
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $response = new Response(200);
        $this->assertions->assertParsedBodyEquals($this, $request, $response);
    }

    public function testAssertParsedBodyPassesCallbackWithBodyThatCannotBeNegotiatedThrows(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to parse the response body');
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{}');
        $response = new Response(200, null, $body);
        $mediaTypeFormatterMatcher = $this->createMock(IMediaTypeFormatterMatcher::class);
        $mediaTypeFormatterMatcher->expects($this->once())
            ->method('getBestResponseMediaTypeFormatterMatch')
            ->with(self::class, $request)
            ->willReturn(null);
        $assertions = new ResponseAssertions($mediaTypeFormatterMatcher);
        $assertions->assertParsedBodyPassesCallback($request, $response, self::class, fn ($parsedBody) => true);
    }

    public function testAssertParsedBodyPassesCallbackWithNonHttpBodyDoesNotThrowOnMatch(): void
    {
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{"foo":"bar"}');
        $response = new Response(200, null, $body);
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->assertions->assertParsedBodyPassesCallback($request, $response, $expectedParsedBody::class, function ($parsedBody) use ($expectedParsedBody) {
            return $parsedBody == $expectedParsedBody;
        });
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertParsedBodyPassesCallbackWithNonHttpBodyThrowOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Failed to assert that the response body passes the callback');
        $request = new Request('GET', new Uri('http://localhost'), new Headers([new KeyValuePair('Accept', 'application/json')]));
        $body = new StringBody('{"foo":"bar"}');
        $response = new Response(200, null, $body);
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->assertions->assertParsedBodyPassesCallback($request, $response, $expectedParsedBody::class, function ($parsedBody) {
            return false;
        });
    }

    public function testAssertStatusCodeEqualsDoesNotThrowOnMatch(): void
    {
        $response = new Response(200);
        $this->assertions->assertStatusCodeEquals(200, $response);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAssertStatusCodeEqualsThrowsOnNoMatch(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected status code 200, got 500');
        $response = new Response(500);
        $this->assertions->assertStatusCodeEquals(200, $response);
    }
}
