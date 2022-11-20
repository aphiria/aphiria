<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api;

use Aphiria\Framework\Api\ApiApplication;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseWriter;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ApiApplicationTest extends TestCase
{
    private ApiApplication $app;
    private IRequestHandler&MockObject $apiGateway;

    protected function setUp(): void
    {
        $this->apiGateway = $this->createMock(IRequestHandler::class);
        $this->app = new ApiApplication($this->apiGateway, fn (): IRequest => $this->createMock(IRequest::class));
    }

    public function testRunReturnsZero(): void
    {
        $this->assertSame(0, $this->app->run());
    }

    public function testRunWritesResponse(): void
    {
        $apiGateway = $this->createMock(IRequestHandler::class);
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $apiGateway->method('handle')
            ->with($request)
            ->willReturn($response);
        $responseWriter = $this->createMock(IResponseWriter::class);
        $responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($response);
        $app = new ApiApplication($apiGateway, fn (): IRequest => $request, $responseWriter);
        $app->run();
    }

    public function testUnhandledExceptionsAreRethrownAsRuntimeExceptions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to run the application');
        $this->apiGateway->method('handle')
            ->willThrowException(new Exception());
        $this->app->run();
    }
}
