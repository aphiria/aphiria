<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Testing\PhpUnit;

use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Testing\ApplicationClient;
use Aphiria\Framework\Testing\AssertionFailedException;
use Aphiria\Framework\Testing\ResponseAssertions;
use Aphiria\Net\Http\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IHttpClient;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Uri;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * Defines a base integration test case
 */
abstract class IntegrationTestCase extends TestCase
{
    /** @var ResponseAssertions The response assertions */
    protected ResponseAssertions $responseAssertions;
    /** @var RequestBuilder The request builder */
    protected RequestBuilder $requestBuilder;
    /** @var IRequest|null The most recently sent request from the helper methods in this class */
    protected ?IRequest $lastRequest = null;
    /**
     * The application client
     * This is private for DX so that we can control setting the last request and using that for any assertions that need it
     *
     * @var IHttpClient
     */
    private IHttpClient $client;

    protected function setUp(): void
    {
        $container = new Container();
        Container::$globalInstance = $container;
        $container->bindInstance([IServiceResolver::class, IContainer::class, Container::class], $container);
        $this->client = $this->createClient($container);
        $this->requestBuilder = $this->createRequestBuilder($container);
        $this->responseAssertions = $this->createResponseAssertions($container);
    }

    /**
     * Asserts that a cookie value matches an expected value
     *
     * @param mixed $expectedValue The expected cookie value
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to inspect
     */
    public function assertCookieEquals($expectedValue, IResponse $response, string $cookieName): void
    {
        try {
            $this->responseAssertions->assertCookieEquals($expectedValue, $response, $cookieName);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response has a cookie
     *
     * @param IResponse $response The response to inspect
     * @param string $cookieName The name of the cookie to look for
     */
    public function assertHasCookie(IResponse $response, string $cookieName): void
    {
        try {
            $this->responseAssertions->assertHasCookie($response, $cookieName);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response has a header
     *
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to look for
     */
    public function assertHasHeader(IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHasHeader($response, $headerName);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a response header equals a value
     *
     * @param mixed $expectedValue The expected header value
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     */
    public function assertHeaderEquals($expectedValue, IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHeaderEquals($expectedValue, $response, $headerName);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that a header value matches a regex
     *
     * @param string $regex The regex to apply
     * @param IResponse $response The response to inspect
     * @param string $headerName The name of the header to inspect
     */
    public function assertHeaderMatchesRegex(string $regex, IResponse $response, string $headerName): void
    {
        try {
            $this->responseAssertions->assertHeaderMatchesRegex($regex, $response, $headerName);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the parsed response body matches the expected value
     *
     * @param mixed $expectedValue The expected value
     * @param IResponse $response The response to inspect
     */
    public function assertParsedBodyEquals($expectedValue, IResponse $response): void
    {
        try {
            $this->responseAssertions->assertParsedBodyEquals($expectedValue, $this->lastRequest, $response);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the parsed response body passes a callback
     *
     * @param IResponse $response The response to inspect
     * @param string $type The type to parse the response body as
     * @param Closure $callback The callback that takes in the parsed body (mixed type) and returns true if it passes, otherwise false
     * @throws SerializationException Thrown if there was an error deserializing the response body
     */
    public function assertParsedBodyPassesCallback(IResponse $response, string $type, Closure $callback): void
    {
        try {
            $this->responseAssertions->assertParsedBodyPassesCallback($this->lastRequest, $response, $type, $callback);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the response status code matches the expected value
     *
     * @param int $expectedStatusCode The expected value
     * @param IResponse $response The response to inspect
     */
    public function assertStatusCodeEquals(int $expectedStatusCode, IResponse $response): void
    {
        try {
            $this->responseAssertions->assertStatusCodeEquals($expectedStatusCode, $response);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Sends a DELETE request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @param mixed $body The body of the request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function delete($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('DELETE')
            ->withUri($uri)
            ->withManyHeaders($headers)
            ->withBody($body)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends a GET request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     */
    public function get($uri, array $headers = []): IResponse
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri($uri)
            ->withManyHeaders($headers)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends an OPTIONS request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @param mixed $body The body of the request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function options($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('OPTIONS')
            ->withUri($uri)
            ->withManyHeaders($headers)
            ->withBody($body)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends a POST request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @param mixed $body The body of the request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function post($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('POST')
            ->withUri($uri)
            ->withManyHeaders($headers)
            ->withBody($body)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends a PUT request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @param mixed $body The body of the request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     * @throws SerializationException Thrown if the body could not be serialized
     */
    public function put($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('PUT')
            ->withUri($uri)
            ->withManyHeaders($headers)
            ->withBody($body)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends a request to the application
     *
     * @param IRequest $request The request to send
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     */
    public function send(IRequest $request): IResponse
    {
        return $this->client->send($this->lastRequest = $request);
    }

    /**
     * Gets the built application that will handle requests
     *
     * @param IContainer $container The DI container
     * @return IRequestHandler The application
     */
    abstract protected function createApplication(IContainer $container): IRequestHandler;

    /**
     * Creates an HTTP client for use in integration tests
     *
     * @param IContainer $container The DI container
     * @return IHttpClient The HTTP client to be used
     */
    protected function createClient(IContainer $container): IHttpClient
    {
        return new ApplicationClient($this->createApplication($container), $container);
    }

    /**
     * Creates a request builder that can be used in integration tests
     *
     * @param IContainer $container The DI container
     * @return RequestBuilder The request builder
     * @throws ResolutionException Thrown if the media type formatter matcher could not be resolved
     */
    protected function createRequestBuilder(IContainer $container): RequestBuilder
    {
        return new RequestBuilder($container->resolve(IMediaTypeFormatterMatcher::class));
    }

    /**
     * Creates response assertions that can be used in integration tests
     *
     * @param IContainer $container The DI container
     * @return ResponseAssertions The response assertions
     * @throws ResolutionException Thrown if the media type formatter matcher could not be resolved
     */
    protected function createResponseAssertions(IContainer $container): ResponseAssertions
    {
        return new ResponseAssertions($container->resolve(IMediaTypeFormatterMatcher::class));
    }
}
