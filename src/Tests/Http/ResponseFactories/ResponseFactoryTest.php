<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ResponseFactories;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\RequestContext;
use Opulence\Net\Http\ResponseFactories\ResponseFactory;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Tests\Http\ResponseFactories\Mocks\User;
use Opulence\Net\Uri;
use Opulence\Serialization\SerializationException;

/**
 * Tests the response factory
 */
class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreatingResponseUsesStatusCodeSetInConstructor(): void
    {
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_ACCEPTED);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertEquals(HttpStatusCodes::HTTP_ACCEPTED, $response->getStatusCode());
    }

    public function testCreatingResponseWithHeadersUsesThoseHeaders(): void
    {
        $headers = new HttpHeaders();
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, $headers, null);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertSame($headers, $response->getHeaders());
    }

    public function testCreatingResponseWithHttpBodyJustUsesThatBody(): void
    {
        $expectedBody = $this->createMock(IHttpBody::class);
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $expectedBody);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertSame($expectedBody, $response->getBody());
    }

    public function testCreatingResponseWithNonScalarNorObjectBodyThrowsException(): void
    {
        $rawBody = function () {
            // Don't do anything
        };
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );

        try {
            $responseFactory->createResponse($requestContext);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex->getPrevious());
        }
    }

    public function testCreatingResponseWithObjectBodyAndNoNegotiatedMediaTypeFormatterThrowsException(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestContext = new RequestContext(
            $this->createRequest('http://foo.com'),
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );

        try {
            $responseFactory->createResponse($requestContext);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, $ex->getResponse()->getStatusCode());
        }
    }

    public function testCreatingResponseWithObjectRethrowsSerializationExceptionAsHttpException(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestMediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $requestMediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(Stream::class), null)
            ->willThrowException(new SerializationException);
        $responseContentNegotiationResult = new ContentNegotiationResult(
            $requestMediaTypeFormatter,
            null,
            null,
            null
        );
        $requestContext = new RequestContext(
            $this->createRequest('http://foo.com'),
            new ContentNegotiationResult(null, null, null, null),
            $responseContentNegotiationResult
        );

        try {
            $responseFactory->createResponse($requestContext);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $ex->getResponse()->getStatusCode());
        }
    }

    public function testCreatingResponseWithObjectBodyWritesToResponseBodyUsingMediaTypeFormatterAndMatchedEncoding(
    ): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestMediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $requestMediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(Stream::class), 'utf-8');
        $responseContentNegotiationResult = new ContentNegotiationResult(
            $requestMediaTypeFormatter,
            null,
            'utf-8',
            null
        );
        $requestContext = new RequestContext(
            $this->createRequest('http://foo.com'),
            new ContentNegotiationResult(null, null, null, null),
            $responseContentNegotiationResult
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertInstanceOf(StreamBody::class, $response->getBody());
    }

    public function testCreatingResponseWithScalarBodyCreatesBodyFromScalar(): void
    {
        $rawBody = 'foo';
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertInstanceOf(StringBody::class, $response->getBody());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testCreatingResponseWillSetContentTypeResponseHeaderFromMediaTypeFormatterMediaType(): void
    {
        $requestContentNegotiationResult = new ContentNegotiationResult(null, null, null, null);
        $responseContentNegotiationResult = new ContentNegotiationResult(
            $this->createMock(IMediaTypeFormatter::class),
            'foo/bar',
            null,
            null
        );
        $requestContext = new RequestContext(
            $this->createRequest('http://foo.com'),
            $requestContentNegotiationResult,
            $responseContentNegotiationResult
        );
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK);
        $response = $responseFactory->createResponse($requestContext);
        $this->assertEquals('foo/bar', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseWithStreamBodyCreatesBodyFromStream(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $responseFactory = new ResponseFactory(HttpStatusCodes::HTTP_OK, null, $rawBody);
        $requestContext = $this->createBasicRequestContext(
            new ContentNegotiationResult(null, null, null, null),
            new ContentNegotiationResult(null, null, null, null)
        );
        $response = $responseFactory->createResponse($requestContext);
        $this->assertInstanceOf(StreamBody::class, $response->getBody());
    }

    /**
     * Creates a basic request context
     *
     * @param ContentNegotiationResult $requestContentNegotiationResult The request content negotiation result
     * @param ContentNegotiationResult $responseContentNegotiationResult The response content negotiation result
     * @return RequestContext The request context
     */
    private function createBasicRequestContext(
        ContentNegotiationResult $requestContentNegotiationResult,
        ContentNegotiationResult $responseContentNegotiationResult
    ): RequestContext {
        return new RequestContext(
            $this->createRequest('http://foo.com'),
            $requestContentNegotiationResult,
            $responseContentNegotiationResult
        );
    }

    /**
     * Creates a request with the input URI
     *
     * @param string $uri The URI to use
     * @return Request The request
     */
    private function createRequest(string $uri): Request
    {
        return new Request('GET', new Uri($uri));
    }
}
