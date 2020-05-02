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
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\RequestBuilder;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * Defines a base integration test case
 */
abstract class IntegrationTestCase extends TestCase
{
    /** @var ApplicationClient The application client */
    protected ApplicationClient $client;
    /** @var ResponseAssertions The response assertions */
    protected ResponseAssertions $responseAssertions;
    /** @var RequestBuilder The request builder */
    protected RequestBuilder $requestBuilder;

    protected function setUp(): void
    {
        $container = new Container();
        Container::$globalInstance = $container;
        $container->bindInstance([IServiceResolver::class, IContainer::class, Container::class], $container);
        $this->client = new ApplicationClient($this->getApp($container), $container);
        $this->requestBuilder = $this->createRequestBuilder($container);
        $this->responseAssertions = new ResponseAssertions($container->resolve(IMediaTypeFormatterMatcher::class));
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
     * @param IRequest $request The request that generated the response (used for content negotiation)
     * @param IResponse $response The response to inspect
     * @throws SerializationException Thrown if there was an error deserializing the response body
     */
    public function assertParsedBodyEquals($expectedValue, IRequest $request, IResponse $response): void
    {
        try {
            $this->responseAssertions->assertParsedBodyEquals($expectedValue, $request, $response);
            $this->assertTrue(true);
        } catch (AssertionFailedException $ex) {
            $this->fail($ex->getMessage());
        }
    }

    /**
     * Asserts that the parsed response body passes a callback
     *
     * @param IRequest $request The request that generated the response (used for content negotiation)
     * @param IResponse $response The response to inspect
     * @param string $type The type to parse the response body as
     * @param Closure $callback The callback that takes in the parsed body (mixed type) and returns true if it passes, otherwise false
     * @throws SerializationException Thrown if there was an error deserializing the response body
     */
    public function assertParsedBodyPassesCallback(IRequest $request, IResponse $response, string $type, Closure $callback): void
    {
        try {
            $this->responseAssertions->assertParsedBodyPassesCallback($request, $response, $type, $callback);
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
     * Gets the built application that will handle requests
     *
     * @param IContainer $container The DI container
     * @return IRequestHandler The application
     */
    abstract protected function getApp(IContainer $container): IRequestHandler;

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
}
