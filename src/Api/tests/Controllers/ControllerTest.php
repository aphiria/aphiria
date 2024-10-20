<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Uri;
use Aphiria\Security\IPrincipal;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    private Controller $controller;
    private IRequest&MockObject $request;
    private IResponseFactory&MockObject $responseFactory;

    protected function setUp(): void
    {
        // Allow us to more easily test the convenience methods
        $this->controller = new class () extends Controller {
            public ?IPrincipal $user {
                get {
                    if (!$this->userAccessor instanceof IUserAccessor) {
                        throw new LogicException('User accessor is not set');
                    }

                    if (!$this->request instanceof IRequest) {
                        throw new LogicException('Request is not set');
                    }

                    return $this->userAccessor->getUser($this->request);
                }
            }

            public function accepted(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::accepted($body, $headers);
            }

            public function badRequest(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::badRequest($body, $headers);
            }

            public function conflict(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::conflict($body, $headers);
            }

            public function created(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::created($uri, $body, $headers);
            }

            public function forbidden(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::forbidden($body, $headers);
            }

            public function found(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::found($uri, $body, $headers);
            }

            public function internalServerError(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::internalServerError($body, $headers);
            }

            public function movedPermanently(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::movedPermanently($uri, $body, $headers);
            }

            public function noContent(?Headers $headers = null): IResponse
            {
                return parent::noContent($headers);
            }

            public function notFound(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::notFound($body, $headers);
            }

            public function ok(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::ok($body, $headers);
            }

            public function readRequestBodyAs(string $type): mixed
            {
                return parent::readRequestBodyAs($type);
            }

            public function unauthorized(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
            {
                return parent::unauthorized($body, $headers);
            }
        };
        $this->request = $this->createMock(IRequest::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->responseFactory->method('createResponse')
            ->with($this->request)
            ->willReturnCallback(function (IRequest $request, HttpStatusCode|int $statusCode, Headers $headers, ?IBody $body): IResponse {
                $this->assertSame($this->request, $request);

                return new Response($statusCode, $headers, $body);
            });
        $this->controller->responseFactory = $this->responseFactory;
    }

    public function testAcceptedCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->accepted($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Accepted, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testBadRequestCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->badRequest($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::BadRequest, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testConflictCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->conflict($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Conflict, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testCreatedWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Created, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testCreatedWithUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->created(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Created, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testForbiddenCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->forbidden($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Forbidden, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testFoundWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Found, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testFoundWithUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->found(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Found, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testGetUserGetsUserFromUserAccessor(): void
    {
        $this->controller->request = $this->request;
        $user = $this->createMock(IPrincipal::class);
        $userAccessor = $this->createMock(IUserAccessor::class);
        $userAccessor->expects($this->once())
            ->method('getUser')
            ->with($this->request)
            ->willReturn($user);
        $this->controller->userAccessor = $userAccessor;
        $this->assertSame($user, $this->controller->user);
    }

    public function testGetUserWithoutRequestSetThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request is not set');
        $this->controller->userAccessor = $this->createMock(IUserAccessor::class);
        // Trying to trigger an exception when accessing the user
        $this->controller->user;
    }

    public function testGetUserWithoutUserSetThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User accessor is not set');
        $this->controller->request = $this->createMock(IRequest::class);
        // Trying to trigger an exception when accessing the user
        $this->controller->user;
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
                // Ensure the body deserializer is set so that we're only testing whether the request is set or not
                $this->controller->bodyDeserializer = $this->createMock(IBodyDeserializer::class);
                $helperCallback();
                $this->fail('Failed to throw exception');
            } catch (LogicException $ex) {
                $this->assertSame('Request is not set', $ex->getMessage());
            }
        }
    }

    public function testInternalServerErrorCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->internalServerError($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::InternalServerError, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testMovedPermanentlyWithStringUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently('https://example.com', $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::MovedPermanently, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testMovedPermanentlyWithUriCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->movedPermanently(new Uri('https://example.com'), $expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::MovedPermanently, $response->statusCode);
        $this->assertSame('https://example.com', $response->headers->getFirst('Location'));
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testNoContentCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedHeaders = new Headers();
        $response = $this->controller->noContent($expectedHeaders);
        $this->assertSame(HttpStatusCode::NoContent, $response->statusCode);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testNotFoundCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->notFound($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::NotFound, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testOkCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->ok($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Ok, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }

    public function testReadingRequestBodyReturnsDeserializedBody(): void
    {
        $bodyDeserializer = $this->createMock(IBodyDeserializer::class);
        $bodyDeserializer->expects($this->once())
            ->method('readRequestBodyAs')
            ->with('foo', $this->request)
            ->willReturn('bar');
        $this->controller->bodyDeserializer = $bodyDeserializer;
        $this->controller->request = $this->request;
        $this->assertSame('bar', $this->controller->readRequestBodyAs('foo'));
    }

    public function testReadingRequestBodyThrowsUnprocessableEntityExceptionWhenItFailsToDeserialize(): void
    {
        try {
            $bodyDeserializer = $this->createMock(IBodyDeserializer::class);
            $bodyDeserializer->expects($this->once())
                ->method('readRequestBodyAs')
                ->with('foo', $this->request)
                ->willThrowException(new SerializationException());
            $this->controller->bodyDeserializer = $bodyDeserializer;
            $this->controller->request = $this->request;
            $this->controller->readRequestBodyAs('foo');
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::UnprocessableEntity, $ex->response->statusCode);
            $this->assertSame('Failed to deserialize request body when resolving body as type foo', $ex->getMessage());
        }
    }

    public function testReadingRequestBodyThrowsUnsupportedMediaTypeExceptionWhenContentNegotiationFails(): void
    {
        try {
            $bodyDeserializer = $this->createMock(IBodyDeserializer::class);
            $bodyDeserializer->expects($this->once())
                ->method('readRequestBodyAs')
                ->with('foo', $this->request)
                ->willThrowException(new FailedContentNegotiationException());
            $this->controller->bodyDeserializer = $bodyDeserializer;
            $this->controller->request = $this->request;
            $this->controller->readRequestBodyAs('foo');
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::UnsupportedMediaType, $ex->response->statusCode);
            $this->assertSame('Failed to negotiate request content with type foo', $ex->getMessage());
        }
    }

    public function testUnauthorizedCreatesCorrectResponse(): void
    {
        $this->controller->request = $this->request;
        $expectedBody = $this->createMock(IBody::class);
        $expectedHeaders = new Headers();
        $response = $this->controller->unauthorized($expectedBody, $expectedHeaders);
        $this->assertSame(HttpStatusCode::Unauthorized, $response->statusCode);
        $this->assertSame($expectedBody, $response->body);
        $this->assertSame($expectedHeaders, $response->headers);
    }
}
