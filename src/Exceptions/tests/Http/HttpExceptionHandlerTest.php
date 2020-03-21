<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests\Http;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Exceptions\Http\HttpExceptionHandler;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Response;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the HTTP exception handler
 */
class HttpExceptionHandlerTest extends TestCase
{
    /** @var IResponseWriter|MockObject */
    private IResponseWriter $responseWriter;
    /** @var IHttpRequestMessage|MockObject */
    private IHttpRequestMessage $request;
    /** @var IResponseFactory|MockObject */
    private IResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->responseWriter = $this->createMock(IResponseWriter::class);
    }

    public function testHavingRequestSetButAnExceptionGetsThrownCausesGenericResponse(): void
    {
        $exceptionHandler = $this->createExceptionHandler(true, true, true);
        $exceptionHandler->registerResponseFactory(
            Exception::class,
            function (Exception $ex) {
                throw new Exception();
            }
        );
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() === null
                    && $response->getHeaders()->count() === 0;
            }));
        $exceptionHandler->handle(new Exception);
    }

    public function testHavingRequestSetButNoResponseFactoryAndNotUsingProblemDetailsCreatesGenericResponse(): void
    {
        $exceptionHandler = $this->createExceptionHandler(false, true, true);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() === null
                    && $response->getHeaders()->count() === 0;
            }));
        $exceptionHandler->handle(new Exception);
    }

    public function testHavingRequestSetButNoResponseFactoryAndUsingProblemDetailsCreatesProblemDetailsResponse(): void
    {
        $exceptionHandler = $this->createExceptionHandler(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $this->responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($this->request, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, null, $this->isInstanceOf(ProblemDetails::class))
            ->willReturn($expectedResponse);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionHandler->handle(new Exception);
    }

    public function testHavingRequestSetWithAResponseFactoryCreatesResponseFromFactory(): void
    {
        $exceptionHandler = $this->createExceptionHandler(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $exceptionHandler->registerResponseFactory(
            Exception::class,
            fn (Exception $ex) => $expectedResponse
        );
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionHandler->handle(new Exception);
    }

    public function testHavingRequestSetWithManyResponseFactoriesCreatesResponseFromFactory(): void
    {
        $exceptionHandler = $this->createExceptionHandler(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $exceptionHandler->registerManyResponseFactories([
            Exception::class => fn (Exception $ex) => $expectedResponse
        ]);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionHandler->handle(new Exception);
    }

    public function testNotHavingRequestSetAndNotUsingProblemDetailsCreatesGenericResponse(): void
    {
        $exceptionHandler = $this->createExceptionHandler(false, false, false);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() === null
                    && $response->getHeaders()->count() === 0;
            }));
        $exceptionHandler->handle(new Exception);
    }

    public function testNotHavingRequestSetAndUsingProblemDetailsCreatesProblemDetailsResponse(): void
    {
        $exceptionHandler = $this->createExceptionHandler(true, false, false);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() !== null
                    && $response->getBody()->readAsString() === '{"type":"https:\/\/tools.ietf.org\/html\/rfc7231#section-6.6.1","title":"An error occurred","detail":null,"status":500,"instance":null}'
                    && $response->getHeaders()->getFirst('Content-Type') === 'application/problem+json';
            }));
        $exceptionHandler->handle(new Exception);
    }

    /**
     * Creates an exception handler
     *
     * @param bool $useProblemDetails Whether or not to use problem details
     * @param bool $setRequest Whether or not to set the request
     * @param bool $setResponseFactory Whether or not to set the response factory
     * @return HttpExceptionHandler The exception handler
     */
    private function createExceptionHandler(
        bool $useProblemDetails,
        bool $setRequest,
        bool $setResponseFactory
    ): HttpExceptionHandler {
        return new HttpExceptionHandler(
            $useProblemDetails,
            $setRequest ? $this->request : null,
            $setResponseFactory ? $this->responseFactory : null,
            $this->responseWriter
        );
    }
}
