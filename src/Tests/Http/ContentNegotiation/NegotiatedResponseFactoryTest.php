<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ResponseFactories;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Tests\Http\ContentNegotiation\Mocks\User;
use Opulence\Net\Uri;
use Opulence\Serialization\SerializationException;

/**
 * Tests the negotiated response factory
 */
class NegotiatedResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var NegotiatedResponseFactory The response factory to test */
    private $factory;
    /** @var IContentNegotiator|\PHPUnit_Framework_MockObject_MockObject The content negotiator */
    private $contentNegotiator;

    public function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->factory = new NegotiatedResponseFactory($this->contentNegotiator);
    }

    public function testCreatingResponseFromArrayUsesTypeOfFirstItemWhenNegotiationContent(): void
    {
        $request = $this->createRequest('http://foo.com');
        $rawBody = [new User(123, 'foo@bar.com'), new User(456, 'bar@baz.com')];
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(IStream::class), 'utf-8');
        $this->setUpContentNegotiationMock(
            User::class . '[]',
            $request,
            new ContentNegotiationResult(
                $mediaTypeFormatter,
                null,
                'utf-8',
                null
            )
        );
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertInstanceOf(StreamBody::class, $response->getBody());
    }

    public function testCreatingResponseFromEmptyArrayWillSetStillNegotiateContent(): void
    {
        $request = $this->createRequest('http://foo.com');
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with([], $this->isInstanceOf(IStream::class), null);
        $this->setUpContentNegotiationMock(
            'array',
            $request,
            new ContentNegotiationResult(
                $mediaTypeFormatter,
                null,
                null,
                null
            )
        );
        $response = $this->factory->createResponse($request, 200, null, []);
        $this->assertNotNull($response->getBody());
    }

    public function testCreatingResponseFromStreamWillSetContentLengthHeader(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $rawBody->expects($this->once())
            ->method('getLength')
            ->willReturn(123);
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertEquals(123, $response->getHeaders()->getFirst('Content-Length'));
    }

    public function testCreatingResponseFromStreamWithUnknownLengthWillNotSetContentLengthHeader(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $rawBody->expects($this->once())
            ->method('getLength')
            ->willReturn(null);
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertFalse($response->getHeaders()->containsKey('Content-Length'));
    }

    public function testCreatingResponseFromStringWillSetContentLengthHeader(): void
    {
        $rawBody = 'foo';
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertEquals(\mb_strlen($rawBody), $response->getHeaders()->getFirst('Content-Length'));
    }

    public function testCreatingResponseFromStringWithAlreadySetContentLengthHeaderDoesNotOverwriteContentLength(): void
    {
        $rawBody = 'foo';
        $headers = new HttpHeaders();
        $headers->add('Content-Length', 123);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, $headers, $rawBody);
        $this->assertEquals(123, $response->getHeaders()->getFirst('Content-Length'));
    }

    public function testCreatingResponseUsesStatusCode(): void
    {
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 202, null, null);
        $this->assertEquals(202, $response->getStatusCode());
    }

    public function testCreatingResponseWillSetContentTypeResponseHeaderFromMediaTypeFormatterMediaType(): void
    {
        $request = $this->createRequest('http://foo.com');
        $this->setUpContentNegotiationMock(
            User::class,
            $request,
            new ContentNegotiationResult(
                $this->createMock(IMediaTypeFormatter::class),
                'foo/bar',
                null,
                null
            )
        );
        $rawBody = new User(123, 'foo@bar.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertEquals('foo/bar', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseWithHeadersUsesThoseHeaders(): void
    {
        $headers = new HttpHeaders();
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, $headers, null);
        $this->assertSame($headers, $response->getHeaders());
    }

    public function testCreatingResponseWithHttpBodyJustUsesThatBody(): void
    {
        $expectedBody = $this->createMock(IHttpBody::class);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $expectedBody);
        $this->assertSame($expectedBody, $response->getBody());
    }

    public function testCreatingResponseWithNonScalarNorObjectBodyThrowsException(): void
    {
        $rawBody = function () {
            // Don't do anything
        };

        try {
            $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $rawBody);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex->getPrevious());
        }
    }

    public function testCreatingResponseWithObjectBodyAndNoNegotiatedMediaTypeFormatterThrowsException(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $request = $this->createRequest('http://foo.com');
        $this->setUpContentNegotiationMock(
            User::class,
            $request,
            new ContentNegotiationResult(null, null, null, null)
        );
        $this->contentNegotiator->expects($this->once())
            ->method('getAcceptableResponseMediaTypes')
            ->willReturn(['foo/bar']);

        try {
            $this->factory->createResponse($request, 200, null, $rawBody);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $response = $ex->getResponse();
            $this->assertEquals(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, $response->getStatusCode());
            $this->assertEquals('application/json', $response->getHeaders()->getFirst('Content-Type'));
            $this->assertEquals('["foo\/bar"]', (string)$response->getBody());
        }
    }

    public function testCreatingResponseWithObjectRethrowsSerializationExceptionAsHttpException(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseMediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $responseMediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(Stream::class), null)
            ->willThrowException(new SerializationException);
        $request = $this->createRequest('http://foo.com');
        $this->setUpContentNegotiationMock(
            User::class,
            $request,
            new ContentNegotiationResult($responseMediaTypeFormatter, null, null, null)
        );

        try {
            $this->factory->createResponse($request, 200, null, $rawBody);
            $this->fail('Expected exception to be thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $ex->getResponse()->getStatusCode());
        }
    }

    public function testCreatingResponseWithObjectBodyWritesToResponseBodyUsingMediaTypeFormatterAndMatchedEncoding(
    ): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseMediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $responseMediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(Stream::class), 'utf-8');
        $request = $this->createRequest('http://foo.com');
        $this->setUpContentNegotiationMock(
            User::class,
            $request,
            new ContentNegotiationResult($responseMediaTypeFormatter, null, 'utf-8', null)
        );
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertInstanceOf(StreamBody::class, $response->getBody());
    }

    public function testCreatingResponseWithScalarBodyCreatesBodyFromScalar(): void
    {
        $rawBody = 'foo';
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $rawBody);
        $this->assertInstanceOf(StringBody::class, $response->getBody());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testCreatingResponseWithStreamBodyCreatesBodyFromStream(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $rawBody);
        $this->assertInstanceOf(StreamBody::class, $response->getBody());
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

    /**
     * Sets up the content negotiatior to return a specific result
     *
     * @param string $expectedType The expected content type
     * @param IHttpRequestMessage $expectedRequest The expected Request
     * @param ContentNegotiationResult $expectedContentNegotiationResult The result to return
     */
    private function setUpContentNegotiationMock(
        string $expectedType,
        IHttpRequestMessage $expectedRequest,
        ContentNegotiationResult $expectedContentNegotiationResult
    ): void {
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateResponseContent')
            ->with($expectedType, $expectedRequest)
            ->willReturn($expectedContentNegotiationResult);
    }
}
