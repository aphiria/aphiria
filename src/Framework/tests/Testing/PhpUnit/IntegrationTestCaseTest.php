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
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use PHPUnit\Framework\TestCase;

class IntegrationTestCaseTest extends TestCase
{
    private IRequestHandler $app;
    private IntegrationTestCase $integrationTests;

    protected function setUp(): void
    {
        $this->app = $this->createMock(IRequestHandler::class);
        $this->integrationTests = new class($this->app) extends IntegrationTestCase {
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

            public function send(IRequest $request): IResponse
            {
                // Make this request accessible by the DI container so the application client doesn't bomb out
                Container::$globalInstance->bindInstance(IRequest::class, $request);

                return parent::send($request);
            }

            // Make this publicly accessible
            public function setUp(): void
            {
                parent::setUp();
            }

            protected function createApplication(IContainer $container): IRequestHandler
            {
                return $this->app;
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
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $expectedParsedBody = new class() {
            public string $foo = 'bar';
        };
        $this->integrationTests->assertParsedBodyEquals($expectedParsedBody, $response);
    }

    public function testAssertParsedBodyEqualsThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyEquals($this, $response);
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
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyPassesCallback(
            $response,
            \get_class($expectedParsedBody),
            fn ($parsedBody) => true
        );
    }

    public function testAssertParsedBodyPassesCallbackThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyPassesCallback(
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

    public function testDeleteSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'DELETE'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar']
                    && (string)$request->getBody() === '{"foo":"bar"}';
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->delete(
            'http://localhost',
            ['Foo' => ['bar']],
            new StringBody('{"foo":"bar"}')
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testGetSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'GET'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar'];
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->get(
            'http://localhost',
            ['Foo' => ['bar']]
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testOptionsSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'OPTIONS'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar']
                    && (string)$request->getBody() === '{"foo":"bar"}';
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->options(
            'http://localhost',
            ['Foo' => ['bar']],
            new StringBody('{"foo":"bar"}')
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testPostSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'POST'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar']
                    && (string)$request->getBody() === '{"foo":"bar"}';
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->post(
            'http://localhost',
            ['Foo' => ['bar']],
            new StringBody('{"foo":"bar"}')
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testPutSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->app->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'PUT'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar']
                    && (string)$request->getBody() === '{"foo":"bar"}';
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->put(
            'http://localhost',
            ['Foo' => ['bar']],
            new StringBody('{"foo":"bar"}')
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }
}
