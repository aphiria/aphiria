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
use Aphiria\Exceptions\Http\HttpExceptionRenderer;
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
 * Tests the HTTP exception renderer
 */
class HttpExceptionRendererTest extends TestCase
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
        $exceptionRenderer = $this->createExceptionRenderer(true, true, true);
        $exceptionRenderer->registerResponseFactory(
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
        $exceptionRenderer->render(new Exception);
    }

    public function testHavingRequestSetButNoResponseFactoryAndNotUsingProblemDetailsCreatesGenericResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, true, true);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() === null
                    && $response->getHeaders()->count() === 0;
            }));
        $exceptionRenderer->render(new Exception);
    }

    public function testHavingRequestSetButNoResponseFactoryAndUsingProblemDetailsCreatesProblemDetailsResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $this->responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($this->request, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, null, $this->isInstanceOf(ProblemDetails::class))
            ->willReturn($expectedResponse);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionRenderer->render(new Exception);
    }

    public function testHavingRequestSetWithAResponseFactoryCreatesResponseFromFactory(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $exceptionRenderer->registerResponseFactory(
            Exception::class,
            fn (Exception $ex) => $expectedResponse
        );
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionRenderer->render(new Exception);
    }

    public function testHavingRequestSetWithManyResponseFactoriesCreatesResponseFromFactory(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, true, true);
        $expectedResponse = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $exceptionRenderer->registerManyResponseFactories([
            Exception::class => fn (Exception $ex) => $expectedResponse
        ]);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $exceptionRenderer->render(new Exception);
    }

    public function testNotHavingRequestSetAndNotUsingProblemDetailsCreatesGenericResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(false, false, false);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() === null
                    && $response->getHeaders()->count() === 0;
            }));
        $exceptionRenderer->render(new Exception);
    }

    public function testNotHavingRequestSetAndUsingProblemDetailsCreatesProblemDetailsResponse(): void
    {
        $exceptionRenderer = $this->createExceptionRenderer(true, false, false);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) {
                return $response->getStatusCode() === HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
                    && $response->getBody() !== null
                    && $response->getBody()->readAsString() === '{"type":"https:\/\/tools.ietf.org\/html\/rfc7231#section-6.6.1","title":"An error occurred","detail":null,"status":500,"instance":null}'
                    && $response->getHeaders()->getFirst('Content-Type') === 'application/problem+json';
            }));
        $exceptionRenderer->render(new Exception);
    }

    /**
     * Creates an exception renderer
     *
     * @param bool $useProblemDetails Whether or not to use problem details
     * @param bool $setRequest Whether or not to set the request
     * @param bool $setResponseFactory Whether or not to set the response factory
     * @return HttpExceptionRenderer The exception renderer
     */
    private function createExceptionRenderer(
        bool $useProblemDetails,
        bool $setRequest,
        bool $setResponseFactory
    ): HttpExceptionRenderer {
        return new HttpExceptionRenderer(
            $useProblemDetails,
            $setRequest ? $this->request : null,
            $setResponseFactory ? $this->responseFactory : null,
            $this->responseWriter
        );
    }
}
