<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use Exception;
use InvalidArgumentException;
use Opulence\Api\Exceptions\ExceptionResponseFactory;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\RequestContext;

/**
 * Tests the exception response factory
 */
class ExceptionResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExceptionResponseFactory The response factory to use in tests */
    private $factory;
    /** @var ExceptionResponseFactoryRegistry The registry to use in tests */
    private $responseFactories;

    public function setUp(): void
    {
        $this->responseFactories = new ExceptionResponseFactoryRegistry();
        $this->factory = new ExceptionResponseFactory($this->responseFactories);
    }

    public function testCreatingResponseForExceptionWithNoRequestContextSetUsesDefaultResponse(): void
    {
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, null);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseForExceptionWithRequestContextAndNoResponseFactoryCreates500Response(): void
    {
        /** @var RequestContext $expectedRequestContext */
        $expectedRequestContext = $this->createMock(RequestContext::class);
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequestContext);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testCreatingResponseForExceptionWithRequestContextAndResponseFactoryCreatesResponseFromFactory(): void
    {
        /** @var RequestContext $expectedRequestContext */
        $expectedRequestContext = $this->createMock(RequestContext::class);
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $this->responseFactories->registerFactory(
            InvalidArgumentException::class,
            function (
                InvalidArgumentException $ex,
                RequestContext $requestContext
            ) use ($expectedRequestContext, $expectedResponse) {
                $this->assertEquals($expectedRequestContext, $requestContext);

                return $expectedResponse;
            }
        );
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequestContext);
        $this->assertSame($expectedResponse, $response);
    }

    public function testCreatingResponseForExceptionWithRequestContextAndResponseFactoryThatThrowsCreatesDefaultResponse(): void
    {
        /** @var RequestContext $expectedRequestContext */
        $expectedRequestContext = $this->createMock(RequestContext::class);
        $this->responseFactories->registerFactory(
            InvalidArgumentException::class,
            function (InvalidArgumentException $ex, RequestContext $requestContext) {
                throw new Exception();
            }
        );
        $response = $this->factory->createResponseFromException(new InvalidArgumentException, $expectedRequestContext);
        $this->assertEquals(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()->getFirst('Content-Type'));
    }

    public function testCreatingResponseForHttpExceptionsUseBuiltInResponseFactory(): void
    {
        // Purposely don't use a registry
        $factory = new ExceptionResponseFactory();
        /** @var RequestContext $expectedRequestContext */
        $expectedRequestContext = $this->createMock(RequestContext::class);
        /** @var IHttpResponseMessage|\PHPUnit_Framework_MockObject_MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $response = $factory->createResponseFromException(new HttpException($expectedResponse), $expectedRequestContext);
        $this->assertSame($expectedResponse, $response);
    }
}
