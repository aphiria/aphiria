<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use Exception;
use InvalidArgumentException;
use Opulence\Api\Exceptions\ExceptionResponseFactory;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\NegotiatedResponseFactory;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the exception response factory
 */
class ExceptionResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExceptionResponseFactory The response factory to use in tests */
    private $factory;
    /** @var NegotiatedResponseFactory The negotiated response factory to use */
    private $negotiatedResponseFactory;
    /** @var IContentNegotiator|MockObject The content negotiator */
    private $contentNegotiator;
    /** @var ExceptionResponseFactoryRegistry The registry to use in tests */
    private $responseFactories;

    public function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->negotiatedResponseFactory = new NegotiatedResponseFactory($this->contentNegotiator);
        $this->responseFactories = new ExceptionResponseFactoryRegistry();
        $this->factory = new ExceptionResponseFactory($this->negotiatedResponseFactory, $this->responseFactories);
    }

    public function testCreatingResponseForExceptionWithNoRequestSetUsesDefaultResponse(): void
    {
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, null);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseForExceptionWithRequestAndNoResponseFactoryCreates500Response(): void
    {
        /** @var IHttpRequestMessage|MockObject $expectedRequest */
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequest);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testCreatingResponseForExceptionWithRequestAndResponseFactoryCreatesResponseFromFactory(): void
    {
        /** @var IHttpRequestMessage|MockObject $expectedRequest */
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $expectedRequest */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $this->responseFactories->registerFactory(
            InvalidArgumentException::class,
            function (
                InvalidArgumentException $ex,
                IHttpRequestMessage $request
            ) use ($expectedRequest, $expectedResponse) {
                $this->assertEquals($expectedRequest, $request);

                return $expectedResponse;
            }
        );
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequest);
        $this->assertSame($expectedResponse, $response);
    }

    public function testCreatingResponseForExceptionWithRequestAndResponseFactoryThatThrowsCreatesDefaultResponse(
    ): void {
        /** @var IHttpRequestMessage|MockObject $expectedRequest */
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        $this->responseFactories->registerFactory(
            InvalidArgumentException::class,
            function (InvalidArgumentException $ex, IHttpRequestMessage $request) {
                throw new Exception();
            }
        );
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequest);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseForHttpExceptionsUseBuiltInResponseFactory(): void
    {
        // Purposely don't use a registry
        $factory = new ExceptionResponseFactory($this->negotiatedResponseFactory);
        /** @var IHttpRequestMessage|MockObject $expectedRequest */
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $response = $factory->createResponseFromException(new HttpException($expectedResponse), $expectedRequest);
        $this->assertSame($expectedResponse, $response);
    }
}
