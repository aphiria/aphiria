<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    private Controller $controller;
    /** @var IRequest|MockObject */
    private IRequest $request;
    /** @var IResponseFactory|MockObject */
    private IResponseFactory $responseFactory;

    protected function setUp(): void
    {
        // Allow us to more easily test the convenience methods
        $this->controller = new class() extends Controller {
            public function badRequest($body = null, Headers $headers = null): IResponse
            {
                return parent::badRequest($body, $headers);
            }

            public function conflict($body = null, Headers $headers = null): IResponse
            {
                return parent::conflict($body, $headers);
            }

            public function created($uri, $body = null, Headers $headers = null): IResponse
            {
                return parent::created($uri, $body, $headers);
            }

            public function forbidden($body = null, Headers $headers = null): IResponse
            {
                return parent::forbidden($body, $headers);
            }

            public function found($uri, $body = null, Headers $headers = null): IResponse
            {
                return parent::found($uri, $body, $headers);
            }

            public function internalServerError($body = null, Headers $headers = null): IResponse
            {
                return parent::internalServerError($body, $headers);
            }

            public function movedPermanently($uri, $body = null, Headers $headers = null): IResponse
            {
                return parent::movedPermanently($uri, $body, $headers);
            }

            public function noContent(Headers $headers = null): IResponse
            {
                return parent::noContent($headers);
            }

            public function notFound($body = null, Headers $headers = null): IResponse
            {
                return parent::notFound($body, $headers);
            }

            public function ok($body = null, Headers $headers = null): IResponse
            {
                return parent::ok($body, $headers);
            }

            public function readRequestBodyAs(string $type)
            {
                return parent::readRequestBodyAs($type);
            }

            public function unauthorized($body = null, Headers $headers = null): IResponse
            {
                return parent::unauthorized($body, $headers);
            }
        };
        $this->request = $this->createMock(IRequest::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->responseFactory->method('createResponse')
            ->with($this->request)
            ->willReturnCallback(function ($request, $statusCode, $headers, $body) {
                $this->assertSame($this->request, $request);

                return new Response($statusCode, $headers, $body);
            });
        $this->controller->setResponseFactory($this->responseFactory);
    }

    public function testBadRequestCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->badRequest($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testConflictCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->conflict($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testCreatedWithInvalidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI must be a string or an instance of ' . Uri::class);
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $this->controller->created([], $expectedBody, $expectedHeaders);
    }

    public function testCreatedWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testCreatedWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testFoundWithInvalidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI must be a string or an instance of ' . Uri::class);
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $this->controller->found([], $expectedBody, $expectedHeaders);
    }

    public function testFoundWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testFoundWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testForbiddenCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->forbidden($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testHelperMethodsWithoutSetRequestThrowsException(): void
    {
        $helperCallbacks = [
            fn () => $this->controller->badRequest(),
            fn () => $this->controller->conflict(),
            fn () => $this->controller->created('https://example.com'),
            fn () => $this->controller->forbidden(),
            fn () => $this->controller->found('https://example.com'),
            fn () => $this->controller->internalServerError(),
            fn () => $this->controller->movedPermanently('https://example.com'),
            fn () => $this->controller->noContent(),
            fn () => $this->controller->notFound(),
            fn () => $this->controller->ok(),
            fn () => $this->controller->readRequestBodyAs('foo'),
            fn () => $this->controller->unauthorized()
        ];

        foreach ($helperCallbacks as $helperCallback) {
            try {
                $helperCallback();
                $this->fail('Failed to throw exception');
            } catch (LogicException $ex) {
                $this->assertEquals('Request is not set', $ex->getMessage());
            }
        }
    }

    public function testInternalServerErrorCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->internalServerError($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testMovedPermanentlyWithInvalidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI must be a string or an instance of ' . Uri::class);
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $this->controller->movedPermanently([], $expectedBody, $expectedHeaders);
    }

    public function testMovedPermanentlyWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testMovedPermanentlyWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertEquals('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testNoContentCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedHeaders = new Headers();
        $response = $this->controller->noContent($expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testNotFoundCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->notFound($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testOkCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->ok($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_OK, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testReadingRequestBodyForArrayTypeWithRequestWithNoBodyReturnsNull(): void
    {
        $this->request->expects($this->once())
            ->method('getBody')
            ->willReturn(null);
        $this->controller->setRequest($this->request);
        $this->assertSame([], $this->controller->readRequestBodyAs('foo[]'));
    }

    public function testReadingRequestBodyForObjectTypeWithRequestWithNoBodyReturnsNull(): void
    {
        $this->request->expects($this->once())
            ->method('getBody')
            ->willReturn(null);
        $this->controller->setRequest($this->request);
        $this->assertNull($this->controller->readRequestBodyAs('foo'));
    }

    public function testReadingRequestBodyForTypeThatThereIsNoMediaTypeFormatterForThrowsException(): void
    {
        try {
            $contentNegotiator = $this->createMock(IContentNegotiator::class);
            $contentNegotiator->expects($this->once())
                ->method('negotiateRequestContent')
                ->with('foo', $this->request)
                ->willReturn(new ContentNegotiationResult(null, null, null, null));
            $this->request->expects($this->once())
                ->method('getBody')
                ->willReturn(new StringBody('foo'));
            $this->controller->setContentNegotiator($contentNegotiator);
            $this->controller->setRequest($this->request);
            $this->controller->readRequestBodyAs('foo');
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE, $ex->getResponse()->getStatusCode());
            $this->assertEquals('Failed to negotiate request content with type foo', $ex->getMessage());
        }
    }

    public function testReadingRequestBodyWithMediaTypeFormatterThatFailsToSerializeThrowsException(): void
    {
        try {
            $expectedBody = new StringBody('foo');
            $this->request->expects($this->once())
                ->method('getBody')
                ->willReturn($expectedBody);
            $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
            $mediaTypeFormatter->expects($this->once())
                ->method('readFromStream')
                ->with($expectedBody->readAsStream(), 'foo')
                ->willThrowException(new SerializationException());
            $contentNegotiator = $this->createMock(IContentNegotiator::class);
            $contentNegotiator->expects($this->once())
                ->method('negotiateRequestContent')
                ->with('foo', $this->request)
                ->willReturn(new ContentNegotiationResult($mediaTypeFormatter, null, null, null));
            $this->request->expects($this->once())
                ->method('getBody')
                ->willReturn(new StringBody('foo'));
            $this->controller->setContentNegotiator($contentNegotiator);
            $this->controller->setRequest($this->request);
            $this->controller->readRequestBodyAs('foo');
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY, $ex->getResponse()->getStatusCode());
            $this->assertEquals('Failed to deserialize request body when resolving body as type foo', $ex->getMessage());
        }
    }

    public function testReadingRequestBodyWithMediaTypeFormatterThatReturnsDeserializedBodyReturnsThatBody(): void
    {
        $expectedBody = new StringBody('foo');
        $this->request->expects($this->once())
            ->method('getBody')
            ->willReturn($expectedBody);
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('readFromStream')
            ->with($expectedBody->readAsStream(), 'foo')
            ->willReturn('bar');
        $contentNegotiator = $this->createMock(IContentNegotiator::class);
        $contentNegotiator->expects($this->once())
            ->method('negotiateRequestContent')
            ->with('foo', $this->request)
            ->willReturn(new ContentNegotiationResult($mediaTypeFormatter, null, null, null));
        $this->request->expects($this->once())
            ->method('getBody')
            ->willReturn(new StringBody('foo'));
        $this->controller->setContentNegotiator($contentNegotiator);
        $this->controller->setRequest($this->request);
        $this->assertEquals('bar', $this->controller->readRequestBodyAs('foo'));
    }

    public function testUnauthorizedCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->unauthorized($expectedBody, $expectedHeaders);
        $this->assertEquals(HttpStatusCodes::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }
}
