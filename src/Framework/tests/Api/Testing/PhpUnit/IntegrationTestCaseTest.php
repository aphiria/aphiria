<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Testing\PhpUnit;

use Aphiria\Application\IApplication;
use Aphiria\Authentication\IAuthenticator;
use Aphiria\Authentication\IMockAuthenticator;
use Aphiria\Collections\KeyValuePair;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\HtmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\XmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\NegotiatedRequestBuilder;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Testing\PhpUnit\IntegrationTestCase;
use Aphiria\Framework\Api\Testing\ResponseAssertions;
use Aphiria\Framework\Authentication\Binders\AuthenticationBinder;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use Aphiria\Security\Identity;
use Aphiria\Security\IPrincipal;
use Aphiria\Security\User;
use Closure;
use DateTime;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[IgnoreMethodForCodeCoverage(IntegrationTestCase::class, 'failWithMessage')]
class IntegrationTestCaseTest extends TestCase
{
    private IRequestHandler&MockObject $apiGateway;
    private IApplication&MockObject $app;
    private (MockObject&IAuthenticator)|(MockObject&IMockAuthenticator)|null $authenticator;
    private IBodyDeserializer&MockObject $bodyDeserializer;
    private IntegrationTestCase $integrationTests;
    private string $prevAppUrl;

    protected function setUp(): void
    {
        $this->prevAppUrl = \getenv('APP_URL') ?: '';
        $this->app = $this->createMock(IApplication::class);
        $this->apiGateway = $this->createMock(IRequestHandler::class);
        $this->bodyDeserializer = $this->createMock(IBodyDeserializer::class);
        $this->authenticator = $this->createMock(IAuthenticator::class);
        /** @psalm-suppress InvalidPropertyAssignmentValue We have to make the authenticator a pretty flexible type to be able to test it */
        $this->integrationTests = new class ($this->app, $this->apiGateway, $this->bodyDeserializer, $this->authenticator) extends IntegrationTestCase {
            private static ?string $failMessage = null;
            private IMediaTypeFormatterMatcher $mediaTypeFormatterMatcher;

            public function __construct(
                private IApplication $app,
                private IRequestHandler $apiGateway,
                private IBodyDeserializer $bodyDeserializer,
                // This has to be set by reference so we can override it when testing mock authentication
                protected IAuthenticator|IMockAuthenticator|null &$authenticator
            ) {
                /** @psalm-suppress InternalMethod We need to call this internal method */
                parent::__construct('foo');

                $this->mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher([
                    new JsonMediaTypeFormatter(),
                    new XmlMediaTypeFormatter(),
                    new HtmlMediaTypeFormatter(),
                    new PlainTextMediaTypeFormatter()
                ]);
            }

            public function actingAs(IPrincipal $user, Closure $callback): mixed
            {
                return parent::actingAs($user, $callback);
            }

            // Make this an instance method just for ease of access (static methods on anon classes are a pain)
            public function getFailMessage(): ?string
            {
                return self::$failMessage;
            }

            public function getLastRequest(): ?IRequest
            {
                return $this->lastRequest;
            }

            public function send(IRequest $request): IResponse
            {
                // Make this request accessible by the DI container so the application client doesn't bomb out
                Container::$globalInstance?->bindInstance(IRequest::class, $request);

                return parent::send($request);
            }

            // Make this public for testability
            public function delete(string|Uri $uri, array $headers = [], mixed $body = null): IResponse
            {
                return parent::delete($uri, $headers, $body);
            }

            // Make this public for testability
            public function get(string|Uri $uri, array $headers = []): IResponse
            {
                return parent::get($uri, $headers);
            }

            // Make this public for testability
            public function options(string|Uri $uri, array $headers = [], mixed $body = null): IResponse
            {
                return parent::options($uri, $headers, $body);
            }

            // Make this public for testability
            public function patch(string|Uri $uri, array $headers = [], mixed $body = null): IResponse
            {
                return parent::patch($uri, $headers, $body);
            }

            // Make this public for testability
            public function post(string|Uri $uri, array $headers = [], mixed $body = null): IResponse
            {
                return parent::post($uri, $headers, $body);
            }

            // Make this public for testability
            public function put(string|Uri $uri, array $headers = [], mixed $body = null): IResponse
            {
                return parent::put($uri, $headers, $body);
            }

            // Make this public for testability
            public function readRequestBodyAs(string $type): float|object|int|bool|array|string|null
            {
                return parent::readRequestBodyAs($type);
            }

            // Make this public for testability
            public function readResponseBodyAs(string $type, IResponse $response): float|object|int|bool|array|string|null
            {
                return parent::readResponseBodyAs($type, $response);
            }

            // Make this public for testability
            public function setUp(): void
            {
                parent::setUp();
            }

            protected function createApplication(IContainer $container): IApplication
            {
                // Ensure that the API gateway is resolvable after this method is invoked
                $container->bindInstance(IRequestHandler::class, $this->apiGateway);

                return $this->app;
            }

            protected function createAuthenticator(IContainer $container): IAuthenticator|IMockAuthenticator|null
            {
                if ($this->authenticator === null) {
                    throw new LogicException('The authenticator was not set in the integration test');
                }

                // Make sure the DI container can resolve this
                $container->bindInstance(IAuthenticator::class, $this->authenticator);

                return parent::createAuthenticator($container);
            }

            protected function createBodyDeserializer(IContainer $container): IBodyDeserializer
            {
                // Ensure that the body negotiator is resolvable after this method is invoked
                $container->bindInstance(IBodyDeserializer::class, $this->bodyDeserializer);

                return parent::createBodyDeserializer($container);
            }

            protected function createRequestBuilder(IContainer $container): NegotiatedRequestBuilder
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

            protected function failWithMessage(string $message): void
            {
                self::$failMessage = $message;
            }
        };
        $this->integrationTests->setUp();
    }

    protected function tearDown(): void
    {
        Container::$globalInstance = null;
        \putenv("APP_URL={$this->prevAppUrl}");
    }

    public static function getFullyQualifiedUris(): array
    {
        $schemes = ['about', 'data', 'file', 'ftp', 'git', 'http', 'https', 'sftp', 'ssh', 'svn'];
        $uris = [];

        foreach ($schemes as $scheme) {
            $uris[] = ["$scheme://localhost/path"];
        }

        return $uris;
    }

    public function testActingAsCallsMockAuthenticator(): void
    {
        $user = new User([new Identity([])]);
        $callback = fn (): bool => true;
        $this->authenticator = $this->createMock(IMockAuthenticator::class);
        $this->authenticator->method('actingAs')
            ->with($user, $callback)
            ->willReturn($callback());
        $this->assertSame($callback(), $this->integrationTests->actingAs($user, $callback));
    }

    public function testActingAsThrowsExceptionIfAuthenticatorIsNotMockable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The bound authenticator does not implement ' . IMockAuthenticator::class . '.  You may have to customize ' . AuthenticationBinder::class . '::inTestingEnvironment().');
        $user = new User([new Identity([])]);
        $this->integrationTests->actingAs($user, fn () => null);
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
        $this->assertSame(
            'Failed to assert that cookie foo has expected value',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertCookieIsUnsetDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200, new Headers([new KeyValuePair('Set-Cookie', 'foo=; Max-Age=0')]));
        $this->integrationTests->assertCookieIsUnset($response, 'foo');
    }

    public function testAssertCookieIsUnsetThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertCookieIsUnset($response, 'foo');
        $this->assertSame(
            'Failed to assert that cookie foo is unset',
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $expectedParsedBody = new class () {
            public string $foo = 'bar';
        };
        $this->integrationTests->assertParsedBodyEquals($expectedParsedBody, $response);
    }

    public function testAssertParsedBodyEqualsThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyEquals($this, $response);
        $this->assertSame(
            'Failed to assert that the response body matches the expected value',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertParsedBodyEqualsWithoutLastRequestSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A request must be sent before calling ' . IntegrationTestCase::class . '::assertParsedBodyEquals');
        $this->integrationTests->assertParsedBodyEquals($this, new Response());
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
        $expectedParsedBody = new class () {
            public string $foo = 'bar';
        };
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyPassesCallback(
            $response,
            $expectedParsedBody::class,
            fn (mixed $parsedBody): bool => true
        );
    }

    public function testAssertParsedBodyPassesCallbackThrowsOnFailure(): void
    {
        $request = new Request('GET', new Uri('http://localhost'));
        $response = new Response(200);
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->integrationTests->send($request);
        $this->integrationTests->assertParsedBodyPassesCallback(
            $response,
            self::class,
            fn (mixed $parsedBody): bool => false
        );
        $this->assertSame(
            'Failed to assert that the response body passes the callback',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testAssertParsedBodyPassesCallbackWithoutLastRequestSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A request must be sent before calling ' . IntegrationTestCase::class . '::assertParsedBodyPassesCallback');
        $this->integrationTests->assertParsedBodyPassesCallback(new Response(), self::class, fn (mixed $body): bool => false);
    }

    public function testAssertStatusCodeEqualsDoesNotThrowOnSuccess(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertStatusCodeEquals(HttpStatusCode::Ok, $response);
    }

    public function testAssertStatusCodeEqualsThrowsOnFailure(): void
    {
        $response = new Response(200);
        $this->integrationTests->assertStatusCodeEquals(HttpStatusCode::InternalServerError, $response);
        $this->assertSame(
            'Expected status code 500, got 200',
            $this->integrationTests->getFailMessage()
        );
    }

    public function testDeleteSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->apiGateway->expects($this->once())
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
        $this->apiGateway->expects($this->once())
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

    public function testNegotiatingRequestBeforeSendingRequestThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A request must be sent before negotiating the request body');
        $this->integrationTests->readRequestBodyAs(DateTime::class);
    }

    public function testNegotiatingRequestBodyReturnsDeserializedValue(): void
    {
        $expectedNegotiatedBody = new DateTime();
        $this->integrationTests->get('http://localhost');
        $this->bodyDeserializer->method('readRequestBodyAs')
            ->with(DateTime::class, $this->integrationTests->getLastRequest())
            ->willReturn($expectedNegotiatedBody);
        $this->assertSame($expectedNegotiatedBody, $this->integrationTests->readRequestBodyAs(DateTime::class));
    }

    public function testNegotiatingResponseBeforeSendingRequestThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A request must be sent before negotiating the response body');
        $this->integrationTests->readResponseBodyAs(DateTime::class, $this->createMock(IResponse::class));
    }

    public function testNegotiatingResponseBodyReturnsDeserializedValue(): void
    {
        $expectedNegotiatedBody = new DateTime();
        $response = $this->integrationTests->get('http://localhost');
        $this->bodyDeserializer->method('readResponseBodyAs')
            ->with(DateTime::class, $this->integrationTests->getLastRequest(), $response)
            ->willReturn($expectedNegotiatedBody);
        $this->assertSame($expectedNegotiatedBody, $this->integrationTests->readResponseBodyAs(DateTime::class, $response));
    }

    public function testOptionsSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->apiGateway->expects($this->once())
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

    public function testPatchSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return $request->getMethod() === 'PATCH'
                    && (string)$request->getUri() === 'http://localhost'
                    && $request->getHeaders()->get('Foo') === ['bar']
                    && (string)$request->getBody() === '{"foo":"bar"}';
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->integrationTests->patch(
            'http://localhost',
            ['Foo' => ['bar']],
            new StringBody('{"foo":"bar"}')
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testPostSendsRequestToClient(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $this->apiGateway->expects($this->once())
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
        $this->apiGateway->expects($this->once())
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

    /**
     * @param string $expectedUri The expected URI
     */
    #[DataProvider('getFullyQualifiedUris')]
    public function testSendingRequestWithFullyQualifiedUrisUseThoseUris(string $expectedUri): void
    {
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) use ($expectedUri) {
                return (string)$request->getUri() === $expectedUri;
            }));
        $this->integrationTests->get($expectedUri);
    }

    /**
     * @param string $appUrl The URL to set as the app URL environment variable
     * @param string $path The relative path
     */
    #[TestWith(['http://localhost', 'path'])]
    #[TestWith(['http://localhost/', 'path'])]
    #[TestWith(['http://localhost', '/path'])]
    #[TestWith(['http://localhost/', '/path'])]
    public function testSendingRequestWithRelativeUriCreatesCorrectUri(string $appUrl, string $path): void
    {
        \putenv("APP_URL=$appUrl");
        $this->apiGateway->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (IRequest $request) {
                return (string)$request->getUri() === 'http://localhost/path';
            }));
        $this->integrationTests->get($path);
    }

    public function testSendingRequestWithRelativeUriWithoutAppUrlEnvironmentVariableSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Environment variable "APP_URL" must be set to use a relative path');
        \putenv('APP_URL=');
        $this->integrationTests->get('/foo');
    }

    public function testStringAndUriInstanceAreAllowedForUri(): void
    {
        $this->integrationTests->get('http://example.com');
        $this->integrationTests->get(new Uri('http://example.com'));
        // Dummy assertion
        $this->assertTrue(true);
    }
}
