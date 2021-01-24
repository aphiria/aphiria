<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\Controller;
use Aphiria\ContentNegotiation\ContentNegotiationResult;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
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
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    private Controller $controller;
    private IRequest|MockObject $request;
    private IResponseFactory|MockObject $responseFactory;

    protected function setUp(): void
    {
        // Allow us to more easily test the convenience methods
        $this->controller = new class() extends Controller {
            public function accepted(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::accepted($body, $headers);
            }

            public function badRequest(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::badRequest($body, $headers);
            }

            public function conflict(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::conflict($body, $headers);
            }

            public function created(string|Uri $uri, object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::created($uri, $body, $headers);
            }

            public function forbidden(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::forbidden($body, $headers);
            }

            public function found(string|Uri $uri, object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::found($uri, $body, $headers);
            }

            public function internalServerError(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::internalServerError($body, $headers);
            }

            public function movedPermanently(string|Uri $uri, object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::movedPermanently($uri, $body, $headers);
            }

            public function noContent(Headers $headers = null): IResponse
            {
                return parent::noContent($headers);
            }

            public function notFound(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::notFound($body, $headers);
            }

            public function ok(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::ok($body, $headers);
            }

            public function readRequestBodyAs(string $type): mixed
            {
                return parent::readRequestBodyAs($type);
            }

            public function unauthorized(object|string|int|float|array $body = null, Headers $headers = null): IResponse
            {
                return parent::unauthorized($body, $headers);
            }
        };
        $this->request = $this->createMock(IRequest::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->responseFactory->method('createResponse')
            ->with($this->request)
            ->willReturnCallback(function (IRequest $request, int $statusCode, Headers $headers, ?IBody $body): IResponse {
                $this->assertSame($this->request, $request);

                return new Response($statusCode, $headers, $body);
            });
        $this->controller->setResponseFactory($this->responseFactory);
    }

    public function testAcceptedCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->accepted($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::ACCEPTED, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testBadRequestCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->badRequest($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::BAD_REQUEST, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testConflictCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->conflict($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::CONFLICT, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testCreatedWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::CREATED, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testCreatedWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::CREATED, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testFoundWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::FOUND, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testFoundWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::FOUND, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testForbiddenCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->forbidden($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testHelperMethodsWithoutSetRequestThrowsException(): void
    {
        $helperCallbacks = [
            fn (): IResponse => $this->controller->accepted(),
            fn (): IResponse => $this->controller->badRequest(),
            fn (): IResponse => $this->controller->conflict(),
            fn (): IResponse => $this->controller->created('https://example.com'),
            fn (): IResponse => $this->controller->forbidden(),
            fn (): IResponse => $this->controller->found('https://example.com'),
            fn (): IResponse => $this->controller->internalServerError(),
            fn (): IResponse => $this->controller->movedPermanently('https://example.com'),
            fn (): IResponse => $this->controller->noContent(),
            fn (): IResponse => $this->controller->notFound(),
            fn (): IResponse => $this->controller->ok(),
            fn (): mixed => $this->controller->readRequestBodyAs('foo'),
            fn (): IResponse => $this->controller->unauthorized()
        ];

        foreach ($helperCallbacks as $helperCallback) {
            try {
                $helperCallback();
                $this->fail('Failed to throw exception');
            } catch (LogicException $ex) {
                $this->assertSame('Request is not set', $ex->getMessage());
            }
        }
    }

    public function testInternalServerErrorCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->internalServerError($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testMovedPermanentlyWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testMovedPermanentlyWithUriCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaders()->getFirst('Location'));
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testNoContentCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedHeaders = new Headers();
        $response = $this->controller->noContent($expectedHeaders);
        $this->assertSame(HttpStatusCodes::NO_CONTENT, $response->getStatusCode());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testNotFoundCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->notFound($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testOkCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->ok($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::OK, $response->getStatusCode());
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
            $this->assertSame(HttpStatusCodes::UNSUPPORTED_MEDIA_TYPE, $ex->getResponse()->getStatusCode());
            $this->assertSame('Failed to negotiate request content with type foo', $ex->getMessage());
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
            $this->assertSame(HttpStatusCodes::UNPROCESSABLE_ENTITY, $ex->getResponse()->getStatusCode());
            $this->assertSame('Failed to deserialize request body when resolving body as type foo', $ex->getMessage());
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
        $this->assertSame('bar', $this->controller->readRequestBodyAs('foo'));
    }

    public function testUnauthorizedCreatesCorrectResponse(): void
    {
        $this->controller->setRequest($this->request);
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->unauthorized($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCodes::UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame($expectedBody, $response->getBody());
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }
}
