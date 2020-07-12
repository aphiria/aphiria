<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Testing;

use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\NegotiatedRequestBuilder;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\IHttpClient;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Uri;
use InvalidArgumentException;

/**
 * Defines functionality to simplify running of integration tests
 */
trait IntegrationTest
{
    /** @var ResponseAssertions The response assertions */
    protected ResponseAssertions $responseAssertions;
    /** @var NegotiatedRequestBuilder The request builder */
    protected NegotiatedRequestBuilder $requestBuilder;
    /** @var IRequest|null The most recently sent request from the helper methods in this class */
    protected ?IRequest $lastRequest = null;
    /**
     * The application client
     * This is private for DX so that we can control setting the last request and using that for any assertions that need it
     *
     * @var IHttpClient
     */
    private IHttpClient $client;

    /**
     * Gets the built application that will handle requests
     *
     * @param IContainer $container The DI container
     * @return IRequestHandler The application
     */
    abstract protected function createApplication(IContainer $container): IRequestHandler;

    /**
     * Initializes dependencies before each test
     *
     * @throws ResolutionException Thrown if dependencies could not be resolved
     */
    protected function beforeEachTest(): void
    {
        $container = new Container();
        Container::$globalInstance = $container;
        $container->bindInstance([IServiceResolver::class, IContainer::class, Container::class], $container);
        $this->client = $this->createClient($container);
        $this->requestBuilder = $this->createRequestBuilder($container);
        $this->responseAssertions = $this->createResponseAssertions($container);
    }

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
     * @return NegotiatedRequestBuilder The request builder
     * @throws ResolutionException Thrown if the media type formatter matcher could not be resolved
     */
    protected function createRequestBuilder(IContainer $container): NegotiatedRequestBuilder
    {
        return new NegotiatedRequestBuilder($container->resolve(IMediaTypeFormatterMatcher::class));
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
    protected function delete($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('DELETE')
            ->withUri($this->createUri($uri))
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
    protected function get($uri, array $headers = []): IResponse
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri($this->createUri($uri))
            ->withManyHeaders($headers)
            ->build();

        return $this->send($request);
    }

    /**
     * Gets the current application's URI
     *
     * @return string|null The app URI if one was set, otherwise null
     */
    protected function getAppUri(): ?string
    {
        $appUrl = \getenv('APP_URL');

        if ($appUrl === false || empty($appUrl)) {
            return null;
        }

        return $appUrl;
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
    protected function options($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('OPTIONS')
            ->withUri($this->createUri($uri))
            ->withManyHeaders($headers)
            ->withBody($body)
            ->build();

        return $this->send($request);
    }

    /**
     * Sends a PATCH request
     *
     * @param string|Uri $uri The URI to request
     * @param array $headers The mapping of header names to values
     * @param mixed $body The body of the request
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error sending the request
     * @throws SerializationException Thrown if the body could not be serialized
     */
    protected function patch($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('PATCH')
            ->withUri($this->createUri($uri))
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
    protected function post($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('POST')
            ->withUri($this->createUri($uri))
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
    protected function put($uri, array $headers = [], $body = null): IResponse
    {
        $request = $this->requestBuilder->withMethod('PUT')
            ->withUri($this->createUri($uri))
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
    protected function send(IRequest $request): IResponse
    {
        return $this->client->send($this->lastRequest = $request);
    }

    /**
     * Creates a fully-qualified URI to be used in requests
     *
     * @param string $uri The URI or relative path to create a URI from
     * @return Uri The URI
     * @throws InvalidArgumentException Thrown if the URI could not be parsed into a URI
     */
    private function createUri(string $uri): Uri
    {
        if (preg_match('/^(about|data|file|ftp|git|http|https|sftp|ssh|svn):\/\//i', $uri) === 1) {
            return new Uri($uri);
        }

        if (($appUrl = $this->getAppUri()) === null) {
            throw new InvalidArgumentException('Environment variable "APP_URL" must be set to use a relative path');
        }

        return new Uri(rtrim($appUrl, '/') . '/' . ltrim($uri, '/'));
    }
}
