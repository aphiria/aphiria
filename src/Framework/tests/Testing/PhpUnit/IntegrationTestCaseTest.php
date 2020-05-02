<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Testing\PhpUnit;

use Aphiria\Collections\KeyValuePair;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Testing\PhpUnit\IntegrationTestCase;
use Aphiria\Framework\Testing\ResponseAssertions;
use Aphiria\Net\Http\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\FormUrlEncodedMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

class IntegrationTestCaseTest extends TestCase
{
    private IntegrationTestCase $integrationTests;

    protected function setUp(): void
    {
        $this->integrationTests = new class($this->createMock(IRequestHandler::class)) extends IntegrationTestCase {
            private static ?string $failMessage = null;
            private IRequestHandler $app;
            private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;

            public function __construct(IRequestHandler $app)
            {
                parent::__construct();

                $this->app = $app;
                $this->mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher([
                    new JsonMediaTypeFormatter(),
                    new FormUrlEncodedMediaTypeFormatter(),
                    new HtmlMediaTypeFormatter(),
                    new PlainTextMediaTypeFormatter()
                ]);
            }

            public static function fail(string $message = ''): void
            {
                self::$failMessage = $message;
            }

            // Make this an instance method just for ease of access (static methods on anon classes are a pain)
            public function getFailMessage(): ?string
            {
                return self::$failMessage;
            }

            // Make this publicly accessible
            public function setUp(): void
            {
                parent::setUp();
            }

            protected function createRequestBuilder(IContainer $container): RequestBuilder
            {
                // Make sure the DI container can resolve this
                $container->bindInstance(IMediaTypeFormatterMatcher::class, $this->mediaTypeFormatterMatcher);

                return parent::createRequestBuilder($container);
            }

            protected function createResponseAssertions(IContainer $container): ResponseAssertions
            {
                // Make sure the DI container can resolve this
                $container->bindInstance(IMediaTypeFormatterMatcher::class, $this->mediaTypeFormatterMatcher);

                return parent::createResponseAssertions($container);
            }

            protected function getApp(IContainer $container): IRequestHandler
            {
                return $this->app;
            }
        };
        $this->integrationTests->setUp();
    }

    protected function tearDown(): void
    {
        Container::$globalInstance = null;
    }

    public function testAssertCookieEqualsDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->integrationTests->assertCookieEquals('bar', $response, 'foo');
    }

    public function testAssertCookieEqualsThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertCookieEquals('bar', $response, 'foo');
        $this->assertEquals(
            'Failed to assert that cookie foo has expected value',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertHasCookieDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=bar')]));
        $this->integrationTests->assertHasCookie($response, 'foo');
    }

    public function testAssertHasCookieThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertHasCookie($response, 'foo');
        $this->assertEquals(
            'Failed to assert that cookie foo is set',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertHasHeaderDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->integrationTests->assertHasHeader($response, 'Foo');
    }

    public function testAssertHasHeaderThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertHasHeader($response, 'Foo');
        $this->assertEquals(
            'Failed to assert that header Foo is set',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertHeaderEqualsDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->integrationTests->assertHeaderEquals(['bar'], $response, 'Foo');
    }

    public function testAssertHeaderEqualsThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertHeaderEquals(['bar'], $response, 'Foo');
        $this->assertEquals(
            'No header value for Foo is set',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertHeaderMatchesRegexDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Foo', 'bar')]));
        $this->integrationTests->assertHeaderMatchesRegex('/^bar$/', $response, 'Foo');
    }

    public function testAssertHeaderMatchesRegexThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertHeaderMatchesRegex('/^bar$/', $response, 'Foo');
        $this->assertEquals(
            'No header value for Foo is set',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertParsedBodyEqualsDoesNotThrowOnSuccess(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost'),
            new Headers([new KeyValuePair('Accept', 'application/json')])
        );
        $response = new Response(
            200,
            new Headers([new KeyValuePair('Foo', 'bar')]),
            new StringBody('{"foo":"bar"}')
        );
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->integrationTests->assertParsedBodyEquals($expectedParsedBody, $request, $response);
    }

    public function testAssertParsedBodyEqualsThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->integrationTests->assertParsedBodyEquals($this, $request, $response);
        $this->assertEquals(
            'Failed to assert that the response body matches the expected value',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertParsedBodyPassesCallbackDoesNotThrowOnSuccess(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost'),
            new Headers([new KeyValuePair('Accept', 'application/json')])
        );
        $response = new Response(
            200,
            new Headers([new KeyValuePair('Foo', 'bar')]),
            new StringBody('{"foo":"bar"}')
        );
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->integrationTests->assertParsedBodyPassesCallback(
            $request,
            $response,
            \get_class($expectedParsedBody),
            fn ($parsedBody) => true
        );
    }

    public function testAssertParsedBodyPassesCallbackThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->integrationTests->assertParsedBodyPassesCallback(
            $request,
            $response,
            self::class,
            fn ($parsedBody) => false
        );
        $this->assertEquals(
            'Failed to assert that the response body passes the callback',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertStatusCodeEqualsDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertStatusCodeEquals(200, $response);
    }

    public function testAssertStatusCodeEqualsThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertStatusCodeEquals(500, $response);
        $this->assertEquals(
            'Expected status code 500, got 200',
            $this->integrationTests->getFailMessage()
        );
    }
}
