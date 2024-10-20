<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\ContentNegotiationResult;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class NegotiatedResponseFactoryTest extends TestCase
{
    private IContentNegotiator&MockObject $contentNegotiator;
    private NegotiatedResponseFactory $factory;

    protected function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->factory = new NegotiatedResponseFactory($this->contentNegotiator);
    }

    public function testCreatingResponseFromArrayUsesTypeOfFirstItemWhenNegotiationContent(): void
    {
        $request = $this->createRequest('http://foo.com');
        $rawBody = [new User(123, 'foo@bar.com'), new User(456, 'bar@baz.com')];
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
        $this->assertInstanceOf(StreamBody::class, $response->body);
    }

    public function testCreatingResponseFromEmptyArrayWillSetStillNegotiateContent(): void
    {
        $request = $this->createRequest('http://foo.com');
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
        $this->assertNotNull($response->body);
    }

    public function testCreatingResponseFromStreamWillSetContentLengthHeader(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $rawBody->method(PropertyHook::get('length'))
            ->willReturn(123);
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertSame(123, $response->headers->getFirst('Content-Length'));
    }

    public function testCreatingResponseFromStreamWithUnknownLengthWillNotSetContentLengthHeader(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $rawBody->method(PropertyHook::get('length'))
            ->willReturn(null);
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertFalse($response->headers->containsKey('Content-Length'));
    }

    public function testCreatingResponseFromStringWillSetContentLengthHeader(): void
    {
        $rawBody = 'foo';
        $request = $this->createRequest('http://foo.com');
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertSame(\mb_strlen($rawBody), $response->headers->getFirst('Content-Length'));
    }

    public function testCreatingResponseFromStringWithAlreadySetContentLengthHeaderDoesNotOverwriteContentLength(): void
    {
        $rawBody = 'foo';
        $headers = new Headers();
        $headers->add('Content-Length', 123);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, $headers, $rawBody);
        $this->assertSame(123, $response->headers->getFirst('Content-Length'));
    }

    public function testCreatingResponseIncludesContentLanguageHeaderIfItIsPresentInContentNegotiationResult(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $request = $this->createRequest('http://foo.com');
        $this->setUpContentNegotiationMock(
            User::class,
            $request,
            new ContentNegotiationResult($this->createMock(IMediaTypeFormatter::class), null, 'utf-8', 'en-US')
        );
        $response = $this->factory->createResponse($request, 200, null, $rawBody);
        $this->assertSame('en-US', $response->headers->getFirst('Content-Language'));
    }

    public function testCreatingResponseUsesStatusCode(): void
    {
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 202);
        $this->assertSame(HttpStatusCode::Accepted, $response->statusCode);
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
        $this->assertSame('foo/bar', $response->headers->getFirst('Content-Type'));
    }

    public function testCreatingResponseWithEnumStatusCodeSetsStatusCodeCorrectly(): void
    {
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), HttpStatusCode::Ok);
        $this->assertSame(HttpStatusCode::Ok, $response->statusCode);
    }

    public function testCreatingResponseWithHeadersUsesThoseHeaders(): void
    {
        $headers = new Headers();
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, $headers);
        $this->assertSame($headers, $response->headers);
    }

    public function testCreatingResponseWithHttpBodyJustUsesThatBody(): void
    {
        $expectedBody = $this->createMock(IBody::class);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $expectedBody);
        $this->assertSame($expectedBody, $response->body);
    }

    public function testCreatingResponseWithIntStatusCodeSetsStatusCodeCorrectly(): void
    {
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200);
        $this->assertSame(HttpStatusCode::Ok, $response->statusCode);
    }

    public function testCreatingResponseWithNonScalarNorObjectBodyThrowsException(): void
    {
        $rawBody = function (): void {
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
            $response = $ex->response;
            $this->assertSame(HttpStatusCode::NotAcceptable, $response->statusCode);
            $this->assertSame('application/json', $response->headers->getFirst('Content-Type'));
            $this->assertSame('["foo\/bar"]', (string)$response->body);
        }
    }

    public function testCreatingResponseWithObjectBodyWritesToResponseBodyUsingMediaTypeFormatterAndMatchedEncoding(): void
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
        $this->assertInstanceOf(StreamBody::class, $response->body);
    }

    public function testCreatingResponseWithObjectRethrowsSerializationExceptionAsHttpException(): void
    {
        $rawBody = new User(123, 'foo@bar.com');
        $responseMediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $responseMediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($rawBody, $this->isInstanceOf(Stream::class), null)
            ->willThrowException(new SerializationException());
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
            $this->assertSame(HttpStatusCode::InternalServerError, $ex->response->statusCode);
        }
    }

    public function testCreatingResponseWithScalarBodyCreatesBodyFromScalar(): void
    {
        $rawBody = 'foo';
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $rawBody);
        $this->assertInstanceOf(StringBody::class, $response->body);
        $this->assertSame('foo', (string)$response->body);
    }

    public function testCreatingResponseWithStreamBodyCreatesBodyFromStream(): void
    {
        $rawBody = $this->createMock(IStream::class);
        $response = $this->factory->createResponse($this->createRequest('http://foo.com'), 200, null, $rawBody);
        $this->assertInstanceOf(StreamBody::class, $response->body);
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
     * @param IRequest $expectedRequest The expected Request
     * @param ContentNegotiationResult $expectedContentNegotiationResult The result to return
     */
    private function setUpContentNegotiationMock(
        string $expectedType,
        IRequest $expectedRequest,
        ContentNegotiationResult $expectedContentNegotiationResult
    ): void {
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateResponseContent')
            ->with($expectedType, $expectedRequest)
            ->willReturn($expectedContentNegotiationResult);
    }
}
