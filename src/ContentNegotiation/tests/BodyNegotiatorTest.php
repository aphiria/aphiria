<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\BodyNegotiator;
use Aphiria\ContentNegotiation\ContentNegotiationResult;
use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BodyNegotiatorTest extends TestCase
{
    private BodyNegotiator $bodyNegotiator;
    private IContentNegotiator&MockObject $contentNegotiator;

    protected function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->bodyNegotiator = new BodyNegotiator($this->contentNegotiator);
    }

    public function testDeserializationExceptionGetsThrownWhenDeserializingRequestBody(): void
    {
        $this->expectException(SerializationException::class);
        $request = $this->createMock(IRequest::class);
        $request->method('getBody')
            ->willReturn($this->createMock(IBody::class));
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->method('readFromStream')
            ->willThrowException(new SerializationException());
        $contentNegotiationResult = new ContentNegotiationResult($mediaTypeFormatter, null, null, null);
        $this->contentNegotiator->method('negotiateRequestContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $this->bodyNegotiator->negotiateRequestBody(User::class, $request);
    }

    public function testDeserializationExceptionGetsThrownWhenUnableToReadResponseBody(): void
    {
        $this->expectException(SerializationException::class);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')
            ->willReturn($this->createMock(IBody::class));
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->method('readFromStream')
            ->willThrowException(new SerializationException());
        $contentNegotiationResult = new ContentNegotiationResult($mediaTypeFormatter, null, null, null);
        $this->contentNegotiator->method('negotiateResponseContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $this->bodyNegotiator->negotiateResponseBody(User::class, $request, $response);
    }

    public function testFailingToFindMediaTypeFormatterForRequestBodyThrowsException(): void
    {
        $this->expectException(FailedContentNegotiationException::class);
        $this->expectExceptionMessage('No media type formatter available for ' . User::class);
        $request = $this->createMock(IRequest::class);
        $request->method('getBody')
            ->willReturn($this->createMock(IBody::class));
        $contentNegotiationResult = new ContentNegotiationResult(null, null, null, null);
        $this->contentNegotiator->method('negotiateRequestContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $this->bodyNegotiator->negotiateRequestBody(User::class, $request);
    }

    public function testFailingToFindMediaTypeFormatterForResponseBodyThrowsException(): void
    {
        $this->expectException(FailedContentNegotiationException::class);
        $this->expectExceptionMessage('No media type formatter available for ' . User::class);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')
            ->willReturn($this->createMock(IBody::class));
        $contentNegotiationResult = new ContentNegotiationResult(null, null, null, null);
        $this->contentNegotiator->method('negotiateResponseContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $this->bodyNegotiator->negotiateResponseBody(User::class, $request, $response);
    }

    public function testNegotiatingNullRequestBodyReturnsNull(): void
    {
        $request = $this->createMock(IRequest::class);
        $request->method('getBody')
            ->willReturn(null);
        $actualUser = $this->bodyNegotiator->negotiateRequestBody(User::class, $request);
        $this->assertNull($actualUser);
    }

    public function testNegotiatingNullResponseBodyReturnsNull(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')
            ->willReturn(null);
        $actualUser = $this->bodyNegotiator->negotiateResponseBody(User::class, $request, $response);
        $this->assertNull($actualUser);
    }

    public function testNegotiatingRequestBodyReturnsAnInstanceOfType(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $requestBody = $this->createMock(IBody::class);
        $requestBody->method('readAsStream')
            ->willReturn($this->createMock(IStream::class));
        $request = $this->createMock(IRequest::class);
        $request->method('getBody')
            ->willReturn($requestBody);
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->method('readFromStream')
            ->with($requestBody->readAsStream(), User::class)
            ->willReturn($expectedUser);
        $contentNegotiationResult = new ContentNegotiationResult($mediaTypeFormatter, null, null, null);
        $this->contentNegotiator->method('negotiateRequestContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $actualUser = $this->bodyNegotiator->negotiateRequestBody(User::class, $request);
        $this->assertSame($expectedUser, $actualUser);
    }

    public function testNegotiatingResponseBodyReturnsAnInstanceOfType(): void
    {
        $expectedUser = new User(123, 'foo@bar.com');
        $request = $this->createMock(IRequest::class);
        $responseBody = $this->createMock(IBody::class);
        $responseBody->method('readAsStream')
            ->willReturn($this->createMock(IStream::class));
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')
            ->willReturn($responseBody);
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->method('readFromStream')
            ->with($responseBody->readAsStream(), User::class)
            ->willReturn($expectedUser);
        $contentNegotiationResult = new ContentNegotiationResult($mediaTypeFormatter, null, null, null);
        $this->contentNegotiator->method('negotiateResponseContent')
            ->with(User::class, $request)
            ->willReturn($contentNegotiationResult);
        $actualUser = $this->bodyNegotiator->negotiateResponseBody(User::class, $request, $response);
        $this->assertSame($expectedUser, $actualUser);
    }
}
